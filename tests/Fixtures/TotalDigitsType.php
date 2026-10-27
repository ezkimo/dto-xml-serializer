<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

use MMNewmedia\Model\ValueableInterface;
use MMNewmedia\Model\ValueableTrait;
use MMNewmedia\Xsd;

#[Xsd\SimpleType(name: 'TotalDigitsType')]
#[Xsd\Restriction(base: 'xsd:decimal')]
#[Xsd\TotalDigits(12)]
#[Xsd\FractionDigits(2)]
final class TotalDigitsType implements ValueableInterface
{
    use ValueableTrait;
}
