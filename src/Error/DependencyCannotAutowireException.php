<?php
declare(strict_types=1);

namespace Raxos\Container\Error;

use Raxos\Container\{Dependency, DependencyChain};
use Raxos\Foundation\Error\ExceptionId;

/**
 * Class DependencyCannotAutowireException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Container\Error
 * @since 2.0.0
 */
final class DependencyCannotAutowireException extends ContainerException
{

    /**
     * DependencyCannotAutowireException constructor.
     *
     * @param DependencyChain|null $chain
     * @param Dependency|null $dependency
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public readonly ?DependencyChain $chain,
        public readonly ?Dependency $dependency
    )
    {
        parent::__construct(
            ExceptionId::guess(),
            'container_dependency_cannot_autowire',
            'Cannot autowire dependency.'
        );
    }

}
