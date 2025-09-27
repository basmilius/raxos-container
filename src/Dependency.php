<?php
declare(strict_types=1);

namespace Raxos\Container;

use Closure;
use Raxos\Contract\Reflection\ReflectorInterface;
use Raxos\Reflection\ClassReflector;
use Raxos\Reflection\FunctionReflector;
use Raxos\Reflection\MethodReflector;
use Raxos\Reflection\ParameterReflector;
use Raxos\Reflection\TypeReflector;
use ReflectionException;
use function array_key_last;
use function explode;
use function is_string;

/**
 * Class Dependency
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Container
 * @since 2.0.0
 */
final readonly class Dependency
{

    public string $name;
    public string $shortName;
    public string $typeName;

    /**
     * Dependency constructor.
     *
     * @param ReflectorInterface|Closure|string $dep
     *
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public ReflectorInterface|Closure|string $dep
    )
    {
        $this->name = $this->resolveName($dep);
        $this->shortName = $this->resolveShortName($dep);
        $this->typeName = $this->resolveTypeName($dep);
    }

    /**
     * Returns TRUE if the dependencies are equal.
     *
     * @param Dependency $other
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function equals(self $other): bool
    {
        return $this->name === $other->name;
    }

    /**
     * Resolve the name of the dependency.
     *
     * @param ReflectorInterface|Closure|string $dep
     *
     * @return string
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function resolveName(ReflectorInterface|Closure|string $dep): string
    {
        if (is_string($dep)) {
            return $dep;
        }

        return match ($dep::class) {
            ClassReflector::class => $dep->getName(),
            FunctionReflector::class => $dep->getName() . ' in ' . $dep->getFileName() . ':' . $dep->getStartLine(),
            MethodReflector::class => $dep->getClass()->getName() . '::' . $dep->getName(),
            ParameterReflector::class => $dep->getType()->getName(),
            TypeReflector::class => $dep->getName(),
            default => 'unknown'
        };
    }

    /**
     * Resolve the short name of the dependency.
     *
     * @param ReflectorInterface|Closure|string $dep
     *
     * @return string
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function resolveShortName(ReflectorInterface|Closure|string $dep): string
    {
        if (is_string($dep)) {
            return $dep;
        }

        return match ($dep::class) {
            ClassReflector::class => $dep->getShortName(),
            FunctionReflector::class => $dep->getShortName() . ' in ' . $dep->getFileName() . ':' . $dep->getStartLine(),
            MethodReflector::class => $dep->getClass()->getShortName() . '::' . $dep->getShortName(),
            ParameterReflector::class => $dep->getType()->getShortName(),
            TypeReflector::class => $dep->getShortName(),
            default => 'unknown'
        };
    }

    /**
     * Resolve the type name of the dependency.
     *
     * @param ReflectorInterface|Closure|string $dep
     *
     * @return string
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function resolveTypeName(ReflectorInterface|Closure|string $dep): string
    {
        if (is_string($dep)) {
            $parts = explode('\\', $dep);

            return $parts[array_key_last($parts)];
        }

        return match ($dep::class) {
            ClassReflector::class => $dep->getType()->getShortName(),
            MethodReflector::class => $dep->getClass()->getType()->getShortName(),
            ParameterReflector::class => $dep->getType()->getShortName(),
            TypeReflector::class => $dep->getShortName(),
            default => 'unknown'
        };
    }

}
