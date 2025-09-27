<?php
declare(strict_types=1);

namespace Raxos\Container\Attribute;

use Attribute;
use Raxos\Contract\Container\AttributeInterface;
use UnitEnum;

/**
 * Class Inject
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Container\Attribute
 * @since 2.0.0
 */
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
final readonly class Inject implements AttributeInterface
{

    /**
     * Inject constructor.
     *
     * @param UnitEnum|string|null $tag
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public UnitEnum|string|null $tag = null
    ) {}

}
