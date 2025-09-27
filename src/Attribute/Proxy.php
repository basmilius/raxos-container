<?php
declare(strict_types=1);

namespace Raxos\Container\Attribute;

use Attribute;
use Raxos\Contract\Container\AttributeInterface;

/**
 * Class Proxy
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Container\Attribute
 * @since 2.0.0
 */
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
final readonly class Proxy implements AttributeInterface {}
