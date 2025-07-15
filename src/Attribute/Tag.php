<?php
declare(strict_types=1);

namespace Raxos\Container\Attribute;

use Attribute;
use Raxos\Container\Contract\AttributeInterface;
use UnitEnum;

/**
 * Class Tag
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Container\Attribute
 * @since 2.0.0
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Tag implements AttributeInterface
{

    /**
     * Tag constructor.
     *
     * @param string|UnitEnum $name
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public string|UnitEnum $name
    ) {}

}
