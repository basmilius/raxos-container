<?php
declare(strict_types=1);

namespace Raxos\Container\Error;

use Raxos\Foundation\Error\ExceptionId;
use Throwable;

/**
 * Class AutowireFailedException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Container\Error
 * @since 2.0.0
 */
final class AutowireFailedException extends ContainerException
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
            ExceptionId::guess(),
            'container_autowire_failed',
            'Autowire failed due to an exception.',
            $this->err
        );
    }

}
