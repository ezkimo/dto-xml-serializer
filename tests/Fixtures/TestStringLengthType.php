<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

use MMNewmedia\Xsd;

#[Xsd\ComplexType]
#[Xsd\Schema(
    targetNamespace: 'urn:example:invoice',
    xmlns: 'urn:example:invoice',
)]
#[Xsd\RootElement(namespace: Namespaces::INV, name: 'TestStringLength', type: TestStringLengthType::class)]
final readonly class TestStringLengthType
{
    public function __construct(
        #[Xsd\Element(namespace: Namespaces::CBC, name: 'MinLength', type: MinLengthType::class)]
        #[Xsd\Sequence(position: 1)]
        public MinLengthType $minLength,
        #[Xsd\Element(namespace: Namespaces::CBC, name: 'MaxLength', type: MaxLengthType::class)]
        #[Xsd\Sequence(position: 2)]
        public MaxLengthType $maxLength,
        #[Xsd\Element(namespace: Namespaces::CBC, name: 'RangeLength', type: RangeLengthType::class)]
        #[Xsd\Sequence(position: 3)]
        public RangeLengthType $rangeLength,
        #[Xsd\Element(namespace: Namespaces::CBC, name: 'InheritedLength', type: InheritedLengthType::class)]
        #[Xsd\Sequence(position: 4)]
        public InheritedLengthType $inheritedLength,
    ) {}
}
