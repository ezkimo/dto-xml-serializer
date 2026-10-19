<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

use MMNewmedia\Xsd;

#[Xsd\ComplexType]
#[Xsd\Schema(
    targetNamespace: 'urn:example:invoice',
    xmlns: 'urn:example:invoice',
)]
#[Xsd\RootElement(namespace: Namespaces::INV, name: 'TestEnumeration', type: TestEnumerationType::class)]
final readonly class TestEnumerationType
{
    public function __construct(
        #[Xsd\Element(namespace: Namespaces::CBC, name: 'CurrencyCode', type: CurrencyCodeType::class)]
        #[Xsd\Sequence(position: 1)]
        public CurrencyCodeType $currencyCode,
        #[Xsd\Element(namespace: Namespaces::CBC, name: 'UncheckedCurrencyCode', type: UncheckedCurrencyCodeType::class)]
        #[Xsd\Sequence(position: 2)]
        public UncheckedCurrencyCodeType $uncheckedCurrencyCode,
    ) {}
}
