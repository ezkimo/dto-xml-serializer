<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

use MMNewmedia\Model\ValueableInterface;
use MMNewmedia\Model\ValueableTrait;
use MMNewmedia\Xsd;

#[Xsd\SimpleType(name: 'MaxLengthType')]
#[Xsd\Restriction(base: 'xsd:string')]
#[Xsd\MaxLength(5)]
final class MaxLengthType implements ValueableInterface
{
    use ValueableTrait;
}
