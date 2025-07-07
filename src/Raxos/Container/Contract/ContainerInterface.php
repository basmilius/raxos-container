<?php
declare(strict_types=1);

namespace Raxos\Container\Contract;

use Raxos\Container\Error\ContainerException;
use UnitEnum;

/**
 * Interface ContainerInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Container\Contract
 * @since 2.0.0
 */
interface ContainerInterface
{

    /**
     * Adds a binding to the container.
     *
     * @param string $abstract
     * @param callable|string|null $concrete
     *
     * @return void
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function bind(string $abstract, callable|string|null $concrete): void;

    /**
     * Adds a binding to the container if one doesn't exist.
     *
     * @param string $abstract
     * @param callable|string|null $concrete
     *
     * @return void
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function bindIf(string $abstract, callable|string|null $concrete): void;

    /**
     * Adds a singleton binding to the container.
     *
     * @param string $abstract
     * @param callable|string|null $concrete
     * @param string|UnitEnum|null $tag
     *
     * @return void
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function singleton(string $abstract, callable|string|null $concrete, string|UnitEnum|null $tag = null): void;

    /**
     * Adds a singleton binding to the container if one doesn't exist.
     *
     * @param string $abstract
     * @param callable|string|null $concrete
     * @param string|UnitEnum|null $tag
     *
     * @return void
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function singletonIf(string $abstract, callable|string|null $concrete, string|UnitEnum|null $tag = null): void;

    /**
     * Unbinds a definition from the container.
     *
     * @param class-string $abstract
     * @param bool $tagged
     *
     * @return void
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function unbind(string $abstract, bool $tagged = false): void;

    /**
     * Returns an instance from the container.
     *
     * @template TClass of object
     *
     * @param class-string<TClass>|string $abstract
     * @param string|UnitEnum|null $tag
     *
     * @return TClass|object
     * @throws ContainerException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function get(string $abstract, string|UnitEnum|null $tag = null): object;

    /**
     * Checks if the container has a binding for the given abstract.
     *
     * @param class-string $abstract
     * @param string|UnitEnum|null $tag
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function has(string $abstract, string|UnitEnum|null $tag = null): bool;

}
