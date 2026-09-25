<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

use MMNewmedia\Xsd;

#[Xsd\ComplexType]
#[Xsd\Schema(targetNamespace: 'urn:example:commonaggregate')]
final readonly class PartyType
{
    public function __construct(
        #[Xsd\Element(namespace: Namespaces::CBC, name: 'PartyName', type: NameType::class)]
        #[Xsd\Sequence(position: 1)]
        public NameType $partyName,
        #[Xsd\Element(namespace: Namespaces::CBC, name: 'PartyRole', type: CodeType::class)]
        #[Xsd\Sequence(position: 2)]
        public CodeType $partyRole,
    ) {}
}