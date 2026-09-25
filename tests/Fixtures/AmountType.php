<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

use MMNewmedia\Model\ValueableInterface;
use MMNewmedia\Model\ValueableTrait;
use MMNewmedia\Xsd;

#[Xsd\SimpleContent]
#[Xsd\Extension(base: 'xsd:decimal')]
final class AmountType implements ValueableInterface
{
    use ValueableTrait;

    public function __construct(
        #[Xsd\Attribute(name: 'currencyID', type: 'xsd:string', use: Xsd\AttributeUseType::OPTIONAL)]
        public ?string $currencyID = null,
    ) {}
}