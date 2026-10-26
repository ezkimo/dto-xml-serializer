<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

use MMNewmedia\Model\ValueableInterface;
use MMNewmedia\Model\ValueableTrait;
use MMNewmedia\Xsd;

#[Xsd\SimpleType(name: 'MinExclusiveType')]
#[Xsd\Restriction(base: 'xsd:decimal')]
#[Xsd\MinExclusive('0')]
final class MinExclusiveType implements ValueableInterface
{
    use ValueableTrait;
}
