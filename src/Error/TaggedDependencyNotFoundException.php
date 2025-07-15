<?php
declare(strict_types=1);

namespace Raxos\Container\Error;

use Raxos\Container\{Dependency, DependencyChain};
use Raxos\Foundation\Error\ExceptionId;

/**
 * Class TaggedDependencyNotFoundException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Container\Error
 * @since 2.0.0
 */
final class TaggedDependencyNotFoundException extends ContainerException
{

    /**
     * TaggedDependencyNotFoundException constructor.
     *
     * @param DependencyChain|null $chain
     * @param Dependency|null $dependency
     * @param string $tag
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public readonly ?DependencyChain $chain,
        public readonly ?Dependency $dependency,
        public readonly string $tag
    )
    {
        parent::__construct(
            ExceptionId::guess(),
            'container_tagged_dependency_not_found',
            "Dependency with tag {$tag} not found."
        );
    }

}
