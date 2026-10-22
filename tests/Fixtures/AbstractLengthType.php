<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

use MMNewmedia\Model\ValueableInterface;
use MMNewmedia\Model\ValueableTrait;
use MMNewmedia\Xsd;

#[Xsd\SimpleType(name: 'AbstractLengthType')]
#[Xsd\Restriction(base: 'xsd:string')]
#[Xsd\MinLength(2)]
#[Xsd\MaxLength(10)]
abstract class AbstractLengthType implements ValueableInterface
{
    use ValueableTrait;
}
