<?php
declare(strict_types=1);

namespace Raxos\Container;

use Closure;
use Generator;
use Raxos\Container\Attribute\{Inject, Proxy, Tag};
use Raxos\Container\Contract\ContainerInterface;
use Raxos\Container\Error\{AutowireFailedException, ContainerException, DependencyCannotAutowireException, DependencyCannotInstantiateException, ReflectionFailedException, TaggedDependencyNotFoundException};
use Raxos\Foundation\Reflection\{ClassReflector, FunctionReflector, MethodReflector, ParameterReflector, TypeReflector};
use ReflectionException;
use Throwable;
use UnitEnum;
use function array_filter;
use function debug_backtrace;
use function iterator_to_array;
use function str_starts_with;
use const ARRAY_FILTER_USE_BOTH;
use const DEBUG_BACKTRACE_IGNORE_ARGS;

/**
 * Class Container
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Container
 * @since 2.0.0
 */
final class Container implements ContainerInterface
{

    /**
     * Container constructor.
     *
     * @param bool $production
     * @param array<string, callable|string|null> $definitions
     * @param array<string, callable|string|null> $singletons
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public readonly bool $production = false,
        private array $definitions = [],
        private array $singletons = []
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function bind(string $abstract, callable|string|null $concrete): void
    {
        $this->definitions[$abstract] = $concrete;
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function bindIf(string $abstract, callable|string|null $concrete): void
    {
        if ($this->has($abstract)) {
            return;
        }

        $this->bind($abstract, $concrete);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function singleton(string $abstract, callable|string|null $concrete, string|UnitEnum|null $tag = null): void
    {
        $abstract = $this->resolveTaggedIdentifier($abstract, $tag);

        $this->singletons[$abstract] = $concrete;
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function singletonIf(string $abstract, callable|string|null $concrete, string|UnitEnum|null $tag = null): void
    {
        if ($this->has($abstract, $tag)) {
            return;
        }

        $this->singleton($abstract, $concrete, $tag);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function unbind(string $abstract, bool $tagged = false): void
    {
        unset($this->definitions[$abstract], $this->singletons[$abstract]);

        if (!$tagged) {
            return;
        }

        $this->singletons = array_filter(
            array: $this->singletons,
            callback: static fn(mixed $_, string $key) => !str_starts_with($key, "{$abstract}@"),
            mode: ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function get(string $abstract, string|UnitEnum|null $tag = null): object
    {
        return $this->resolve($abstract, $tag);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function has(string $abstract, string|UnitEnum|null $tag = null): bool
    {
        return isset($this->definitions[$abstract])
            || isset($this->singletons[$this->resolveTaggedIdentifier($abstract, $tag)]);
    }

    /**
     * Autowire a dependency.
     *
     * @param DependencyChain|null $chain
     * @param string $abstract
     *
     * @return object
     * @throws ContainerException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function autowire(?DependencyChain $chain, string $abstract): object
    {
        try {
            $classRef = new ClassReflector($abstract);
            $constructorRef = $classRef->getConstructor();

            if (!$classRef->isInstantiable()) {
                throw new DependencyCannotInstantiateException($chain, new Dependency($abstract));
            }

            $instance = $constructorRef === null
                ? $classRef->newInstanceWithoutConstructor()
                : $classRef->newInstanceArgs(iterator_to_array($this->autowireDependencies($chain, $constructorRef)));

            foreach ($classRef->getProperties() as $property) {
                $inject = $property->getAttribute(Inject::class);

                if ($inject === null || $property->isInitialized($instance)) {
                    continue;
                }

                if ($property->hasAttribute(Proxy::class)) {
                    $property->setValue(
                        $instance,
                        $property->getType()->class()->reflection->newLazyProxy(
                            static fn() => $this->get($property->getType()->getName(), $inject->tag)
                        )
                    );
                } else {
                    $property->setValue($instance, $this->get($property->getType()->getName(), $inject->tag));
                }
            }

            return $instance;
        } catch (ReflectionException $err) {
            throw new ReflectionFailedException($err);
        }
    }

    /**
     * Autowire the dependencies of a function or method.
     *
     * @param DependencyChain|null $chain
     * @param FunctionReflector|MethodReflector $method
     *
     * @return Generator
     * @throws ContainerException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function autowireDependencies(?DependencyChain $chain, FunctionReflector|MethodReflector $method): Generator
    {
        try {
            $chain?->add($method);

            foreach ($method->getParameters() as $parameter) {
                yield $this->autowireDependency(
                    clone $chain,
                    $parameter,
                    $parameter->getAttribute(Tag::class)?->name
                );
            }
        } catch (ReflectionException $err) {
            throw new ReflectionFailedException($err);
        }
    }

    /**
     * Autowire a parameter.
     *
     * @param DependencyChain|null $chain
     * @param ParameterReflector $parameter
     * @param string|UnitEnum|null $tag
     *
     * @return mixed
     * @throws ContainerException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function autowireDependency(?DependencyChain $chain, ParameterReflector $parameter, string|UnitEnum|null $tag = null): mixed
    {
        try {
            $parameterType = $parameter->getType();

            if ($parameterType->isBuiltIn()) {
                return $this->autowireBuiltInDependency($chain, $parameter);
            }

            $lastException = null;
            $proxy = $parameter->hasAttribute(Proxy::class);

            foreach ($parameterType->split() as $typeRef) {
                try {
                    return $this->autowireObjectDependency($typeRef, $tag, $proxy);
                } catch (Throwable $err) {
                    $lastException = $err;
                }
            }

            if ($parameter->hasDefaultValue()) {
                return $parameter->getDefaultValue();
            }

            throw $lastException ?? new DependencyCannotInstantiateException($chain, new Dependency($parameter));
        } catch (ReflectionException $err) {
            throw new ReflectionFailedException($err);
        } catch (Throwable $err) {
            if ($err instanceof ContainerException) {
                throw $err;
            }

            throw new AutowireFailedException($err);
        }
    }

    /**
     * Autowire a built-in dependency for a parameter.
     *
     * @param DependencyChain|null $chain
     * @param ParameterReflector $parameter
     *
     * @return mixed
     * @throws ContainerException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function autowireBuiltInDependency(?DependencyChain $chain, ParameterReflector $parameter): mixed
    {
        try {
            $typeRef = $parameter->getType();
            $tag = $parameter->getAttribute(Tag::class)?->name;

            if ($this->has($typeRef->getName(), $tag)) {
                return $this->get($typeRef->getName(), $tag);
            }

            if ($parameter->hasDefaultValue()) {
                return $parameter->getDefaultValue();
            }

            if ($parameter->isVariadic() || $parameter->isIterable()) {
                return [];
            }

            if ($parameter->isOptional()) {
                return null;
            }

            throw new DependencyCannotAutowireException($chain, new Dependency($parameter));
        } catch (ReflectionException $err) {
            throw new ReflectionFailedException($err);
        }
    }

    /**
     * Autowire an object dependency for a parameter.
     *
     * @param TypeReflector $typeRef
     * @param string|UnitEnum|null $tag
     * @param bool $proxy
     *
     * @return object
     * @throws ContainerException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function autowireObjectDependency(TypeReflector $typeRef, string|UnitEnum|null $tag = null, bool $proxy = false): object
    {
        try {
            if ($proxy) {
                return $typeRef
                    ->class()
                    ->reflection
                    ->newLazyProxy(fn() => $this->resolve($typeRef->getName(), $tag));
            }

            return $this->resolve($typeRef->getName(), $tag);
        } catch (ReflectionException $err) {
            throw new ReflectionFailedException($err);
        }
    }

    /**
     * Resolves a dependency.
     *
     * @param string $abstract
     * @param string|UnitEnum|null $tag
     *
     * @return object
     * @throws ContainerException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function resolve(string $abstract, string|UnitEnum|null $tag = null): object
    {
        try {
            $chain = $this->createChain();
            $classRef = new ClassReflector($abstract);
            $identifier = $this->resolveTaggedIdentifier($abstract, $tag);

            // Singletons
            if (isset($this->singletons[$identifier])) {
                $instance = $this->singletons[$identifier];

                if ($instance instanceof Closure) {
                    $instance = $instance($this);
                    $this->singletons[$identifier] = $instance;
                }

                $chain?->add($classRef);

                return $instance;
            }

            // Closure or instance definition.
            if (isset($this->definitions[$abstract])) {
                $definition = $this->definitions[$abstract];

                if ($definition instanceof Closure) {
                    $chain?->add(new FunctionReflector($definition));

                    return $definition($this);
                }
            }

            // If we don't have a tagged dependency at this point, we don't have it.
            if ($tag !== null) {
                throw new TaggedDependencyNotFoundException($chain, new Dependency($abstract), $tag);
            }

            return $this->autowire($chain, $abstract);
        } catch (ReflectionException $err) {
            throw new ReflectionFailedException($err);
        }
    }

    /**
     * Resolves the tagged identifier for the abstract.
     *
     * @param string $abstract
     * @param string|UnitEnum|null $tag
     *
     * @return string
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function resolveTaggedIdentifier(string $abstract, string|UnitEnum|null $tag = null): string
    {
        if ($tag instanceof UnitEnum) {
            $tag = $tag->name;
        }

        return $tag ? "{$abstract}@{$tag}" : $abstract;
    }

    /**
     * Creates a new dependency chain.
     *
     * @return DependencyChain|null
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function createChain(): ?DependencyChain
    {
        if ($this->production) {
            return null;
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, limit: 2);

        return new DependencyChain($trace[1]['file'], $trace[1]['line']);
    }

}
