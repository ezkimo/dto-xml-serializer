<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

use MMNewmedia\Xsd;

#[Xsd\ComplexType]
#[Xsd\Schema(targetNamespace: 'urn:example:invoice')]
#[Xsd\RootElement(namespace: Namespaces::INV, name: 'TestChoice')]
final readonly class TestChoiceType
{
    public function __construct(
        #[Xsd\Element(namespace: Namespaces::CBC, name: 'BankCard', type: 'xsd:string')]
        #[Xsd\Choice(group: 'payment')]
        #[Xsd\Sequence(position: 1)]
        public ?string $bankCard = null,

        #[Xsd\Element(namespace: Namespaces::CBC, name: 'DirectDebit', type: 'xsd:string')]
        #[Xsd\Choice(group: 'payment')]
        #[Xsd\Sequence(position: 2)]
        public ?string $directDebit = null,

        #[Xsd\Element(namespace: Namespaces::CBC, name: 'Voucher', type: 'xsd:string')]
        #[Xsd\Choice(group: 'discount', minOccurs: 0)]
        #[Xsd\Sequence(position: 3)]
        public ?string $voucher = null,

        #[Xsd\Element(namespace: Namespaces::CBC, name: 'LoyaltyPoints', type: 'xsd:string')]
        #[Xsd\Choice(group: 'discount', minOccurs: 0)]
        #[Xsd\Sequence(position: 4)]
        public ?string $loyaltyPoints = null,
    ) {}
}
