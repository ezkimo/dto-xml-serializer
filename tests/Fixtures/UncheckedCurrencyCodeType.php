<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

use MMNewmedia\Model\ValueableInterface;
use MMNewmedia\Model\ValueableTrait;
use MMNewmedia\Xsd;

#[Xsd\SimpleContent]
#[Xsd\Extension(base: 'xsd:string')]
#[Xsd\Enumeration(enum: CurrencyCode::class, name: 'UncheckedCurrencyCodeType', validate: false)]
final class UncheckedCurrencyCodeType implements ValueableInterface
{
    use ValueableTrait;
}
