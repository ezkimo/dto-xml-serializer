<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

use MMNewmedia\Xsd;

#[Xsd\SimpleType(name: 'InheritedLengthType')]
#[Xsd\Restriction(base: 'xsd:string')]
#[Xsd\MinLength(4)]
#[Xsd\MaxLength(6)]
final class InheritedLengthType extends AbstractLengthType
{
}
