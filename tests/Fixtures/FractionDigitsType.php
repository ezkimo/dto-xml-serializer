<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

use MMNewmedia\Model\ValueableInterface;
use MMNewmedia\Model\ValueableTrait;
use MMNewmedia\Xsd;

#[Xsd\SimpleType(name: 'FractionDigitsType')]
#[Xsd\Restriction(base: 'xsd:decimal')]
#[Xsd\FractionDigits(2)]
final class FractionDigitsType implements ValueableInterface
{
    use ValueableTrait;
}
