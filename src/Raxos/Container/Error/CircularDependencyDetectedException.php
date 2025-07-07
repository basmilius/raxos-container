<?php
declare(strict_types=1);

namespace Raxos\Container\Error;

use Raxos\Container\{Dependency, DependencyChain};
use Raxos\Foundation\Error\ExceptionId;

/**
 * Class CircularDependencyDetectedException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Container\Error
 * @since 2.0.0
 */
final class CircularDependencyDetectedException extends ContainerException
{

    /**
     * CircularDependencyDetectedException constructor.
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
            'container_circular_dependency_detected',
            'Circular dependency detected.'
        );
    }

}
