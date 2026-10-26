<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

use MMNewmedia\Xsd;

#[Xsd\SimpleType(name: 'InheritedDecimalType')]
#[Xsd\Restriction(base: 'xsd:decimal')]
#[Xsd\FractionDigits(2)]
#[Xsd\MaxInclusive('50.00')]
#[Xsd\MinExclusive('-5')]
final class InheritedDecimalType extends AbstractDecimalType
{
}
