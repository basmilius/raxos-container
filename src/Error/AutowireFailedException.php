<?php
declare(strict_types=1);

namespace Raxos\Container\Error;

use Raxos\Contract\Container\ContainerExceptionInterface;
use Raxos\Error\Exception;
use Throwable;

/**
 * Class AutowireFailedException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Container\Error
 * @since 2.0.0
 */
final class AutowireFailedException extends Exception implements ContainerExceptionInterface
{

    /**
     * AutowireFailedException constructor.
     *
     * @param Throwable $err
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public Throwable $err
    )
    {
        parent::__construct(
            'container_autowire_failed',
            'Autowire failed due to an exception.',
            previous: $this->err
        );
    }

}
