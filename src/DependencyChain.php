<?php
declare(strict_types=1);

namespace Raxos\Container;

use Closure;
use Raxos\Container\Error\{CircularDependencyDetectedException, ReflectionFailedException};
use Raxos\Contract\Collection\ArrayableInterface;
use Raxos\Contract\Container\ContainerExceptionInterface;
use Raxos\Contract\DebuggableInterface;
use Raxos\Contract\Reflection\ReflectorInterface;
use ReflectionException;
use function array_key_first;
use function array_key_last;

/**
 * Class DependencyChain
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Container
 * @since 2.0.0
 */
final class DependencyChain implements ArrayableInterface, DebuggableInterface
{

    /** @var Dependency[] */
    private array $dependencies = [];

    /**
     * DependencyChain constructor.
     *
     * @param string $fileName
     * @param int $line
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public readonly string $fileName,
        public readonly int $line
    ) {}

    /**
     * Adds a dependency to the chain.
     *
     * @param ReflectorInterface|Closure|string $dep
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function add(ReflectorInterface|Closure|string $dep): void
    {
        try {
            $dependency = new Dependency($dep);

            if (isset($this->dependencies[$dependency->name])) {
                throw new CircularDependencyDetectedException($this, $dependency);
            }

            $this->dependencies[$dependency->name] = $dependency;
        } catch (ReflectionException $err) {
            throw new ReflectionFailedException($err);
        }
    }

    /**
     * Returns the first dependency in the chain.
     *
     * @return Dependency
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function first(): Dependency
    {
        return $this->dependencies[array_key_first($this->dependencies)];
    }

    /**
     * Returns the last dependency in the chain.
     *
     * @return Dependency
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function last(): Dependency
    {
        return $this->dependencies[array_key_last($this->dependencies)];
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function toArray(): array
    {
        return $this->dependencies;
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __debugInfo(): array
    {
        return [
            'origin' => [
                'file' => $this->fileName,
                'line' => $this->line
            ],
            'dependencies' => $this->dependencies
        ];
    }

}
