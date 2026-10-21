<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

use MMNewmedia\Model\ValueableInterface;
use MMNewmedia\Model\ValueableTrait;
use MMNewmedia\Xsd;

#[Xsd\SimpleType(name: 'PatternType')]
#[Xsd\Restriction(base: 'xsd:string')]
#[Xsd\Pattern(value: '^[A-Z]{3}$')]
final class PatternType implements ValueableInterface
{
    use ValueableTrait;
}
