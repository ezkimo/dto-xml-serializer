<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

use MMNewmedia\Model\ValueableInterface;
use MMNewmedia\Model\ValueableTrait;
use MMNewmedia\Xsd;

#[Xsd\SimpleType(name: 'MaxInclusiveType')]
#[Xsd\Restriction(base: 'xsd:decimal')]
#[Xsd\MaxInclusive('100.00')]
final class MaxInclusiveType implements ValueableInterface
{
    use ValueableTrait;
}
