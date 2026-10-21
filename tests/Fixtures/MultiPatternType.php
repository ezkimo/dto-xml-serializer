<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

use MMNewmedia\Model\ValueableInterface;
use MMNewmedia\Model\ValueableTrait;
use MMNewmedia\Xsd;

#[Xsd\SimpleType(name: 'MultiPatternType')]
#[Xsd\Restriction(base: 'xsd:string')]
#[Xsd\Pattern(value: '^[A-Z]{3}$')]
#[Xsd\Pattern(value: '^[^AEIOU]+$')]
final class MultiPatternType implements ValueableInterface
{
    use ValueableTrait;
}
