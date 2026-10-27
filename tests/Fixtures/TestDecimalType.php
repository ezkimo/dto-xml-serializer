<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

use MMNewmedia\Xsd;

#[Xsd\ComplexType]
#[Xsd\Schema(
    targetNamespace: 'urn:example:invoice',
    xmlns: 'urn:example:invoice',
)]
#[Xsd\RootElement(namespace: Namespaces::INV, name: 'TestDecimal', type: TestDecimalType::class)]
final readonly class TestDecimalType
{
    public function __construct(
        #[Xsd\Element(namespace: Namespaces::CBC, name: 'FractionDigits', type: FractionDigitsType::class)]
        #[Xsd\Sequence(position: 1)]
        public FractionDigitsType $fractionDigits,
        #[Xsd\Element(namespace: Namespaces::CBC, name: 'MaxInclusive', type: MaxInclusiveType::class)]
        #[Xsd\Sequence(position: 2)]
        public MaxInclusiveType $maxInclusive,
        #[Xsd\Element(namespace: Namespaces::CBC, name: 'MinExclusive', type: MinExclusiveType::class)]
        #[Xsd\Sequence(position: 3)]
        public MinExclusiveType $minExclusive,
        #[Xsd\Element(namespace: Namespaces::CBC, name: 'InheritedDecimal', type: InheritedDecimalType::class)]
        #[Xsd\Sequence(position: 4)]
        public InheritedDecimalType $inheritedDecimal,
        #[Xsd\Element(namespace: Namespaces::CBC, name: 'Combined', type: CombinedDecimalType::class)]
        #[Xsd\Sequence(position: 5)]
        public CombinedDecimalType $combined,
        #[Xsd\Element(namespace: Namespaces::CBC, name: 'PreciseMaxInclusive', type: PreciseMaxInclusiveType::class)]
        #[Xsd\Sequence(position: 6)]
        public PreciseMaxInclusiveType $preciseMaxInclusive,
        #[Xsd\Element(namespace: Namespaces::CBC, name: 'MinInclusive', type: MinInclusiveType::class)]
        #[Xsd\Sequence(position: 7)]
        public ?MinInclusiveType $minInclusive = null,
    ) {}
}
