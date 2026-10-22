<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

use MMNewmedia\Model\ValueableInterface;
use MMNewmedia\Model\ValueableTrait;
use MMNewmedia\Xsd;

#[Xsd\SimpleType(name: 'MinLengthType')]
#[Xsd\Restriction(base: 'xsd:string')]
#[Xsd\MinLength(3)]
final class MinLengthType implements ValueableInterface
{
    use ValueableTrait;
}
