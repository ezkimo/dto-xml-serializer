<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

use MMNewmedia\Model\ValueableInterface;
use MMNewmedia\Model\ValueableTrait;
use MMNewmedia\Xsd;

#[Xsd\SimpleType(name: 'MinInclusiveType')]
#[Xsd\Restriction(base: 'xsd:decimal')]
#[Xsd\MinInclusive('0')]
final class MinInclusiveType implements ValueableInterface
{
    use ValueableTrait;
}
