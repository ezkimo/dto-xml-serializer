<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

use MMNewmedia\Model\ValueableInterface;
use MMNewmedia\Model\ValueableTrait;
use MMNewmedia\Xsd;

#[Xsd\SimpleType(name: 'AbstractDecimalType')]
#[Xsd\Restriction(base: 'xsd:decimal')]
#[Xsd\FractionDigits(4)]
#[Xsd\MaxInclusive('100.00')]
#[Xsd\MinExclusive('-10')]
abstract class AbstractDecimalType implements ValueableInterface
{
    use ValueableTrait;
}
