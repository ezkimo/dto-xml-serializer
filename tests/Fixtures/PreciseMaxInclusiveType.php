<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

use MMNewmedia\Model\ValueableInterface;
use MMNewmedia\Model\ValueableTrait;
use MMNewmedia\Xsd;

#[Xsd\SimpleType(name: 'PreciseMaxInclusiveType')]
#[Xsd\Restriction(base: 'xsd:decimal')]
#[Xsd\MaxInclusive('9007199254740993')]
final class PreciseMaxInclusiveType implements ValueableInterface
{
    use ValueableTrait;
}
