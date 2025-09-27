<?php
declare(strict_types=1);

namespace Raxos\Container\Error;

use Raxos\Contract\Container\ContainerExceptionInterface;
use Raxos\Contract\Reflection\ReflectionFailedExceptionInterface;
use Raxos\Error\Exception;
use ReflectionException;

/**
 * Class ReflectionFailedException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Container\Error
 * @since 2.0.0
 */
final class ReflectionFailedException extends Exception implements ContainerExceptionInterface, ReflectionFailedExceptionInterface
{

    /**
     * ReflectionFailedException constructor.
     *
     * @param ReflectionException $err
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public readonly ReflectionException $err
    )
    {
        parent::__construct(
            'container_reflection_failed',
            'Reflection failed.',
            previous: $this->err
        );
    }

}
