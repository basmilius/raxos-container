<?php
declare(strict_types=1);

namespace Raxos\Container\Error;

use Raxos\Container\{Dependency, DependencyChain};
use Raxos\Contract\Container\ContainerExceptionInterface;
use Raxos\Error\Exception;

/**
 * Class TaggedDependencyNotFoundException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Container\Error
 * @since 2.0.0
 */
final class TaggedDependencyNotFoundException extends Exception implements ContainerExceptionInterface
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
            'container_tagged_dependency_not_found',
            "Dependency with tag {$this->tag} not found."
        );
    }

}
