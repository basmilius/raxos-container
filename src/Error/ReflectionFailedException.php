<?php
declare(strict_types=1);

namespace Raxos\Container\Error;

use Raxos\Foundation\Contract\ReflectionFailedExceptionInterface;
use Raxos\Foundation\Error\ExceptionId;
use ReflectionException;

/**
 * Class ReflectionFailedException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Container\Error
 * @since 2.0.0
 */
final class ReflectionFailedException extends ContainerException implements ReflectionFailedExceptionInterface
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
            ExceptionId::guess(),
            'container_reflection_failed',
            'Reflection failed.',
            $this->err
        );
    }

}
