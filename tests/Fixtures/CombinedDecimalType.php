<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

use MMNewmedia\Model\ValueableInterface;
use MMNewmedia\Model\ValueableTrait;
use MMNewmedia\Xsd;

#[Xsd\SimpleType(name: 'CombinedDecimalType')]
#[Xsd\Restriction(base: 'xsd:decimal')]
#[Xsd\FractionDigits(2)]
#[Xsd\MaxInclusive('999.99')]
#[Xsd\MinExclusive('0')]
final class CombinedDecimalType implements ValueableInterface
{
    use ValueableTrait;
}
