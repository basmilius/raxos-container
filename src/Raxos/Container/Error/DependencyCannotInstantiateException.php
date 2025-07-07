<?php
declare(strict_types=1);

namespace Raxos\Container\Error;

use Raxos\Container\{Dependency, DependencyChain};
use Raxos\Foundation\Error\ExceptionId;

/**
 * Class DependencyCannotInstantiateException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Container\Error
 * @since 2.0.0
 */
final class DependencyCannotInstantiateException extends ContainerException
{

    /**
     * DependencyCannotInstantiateException constructor.
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
            'container_cannot_instantiate',
            'Cannot instantiate class.'
        );
    }

}
