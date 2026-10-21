<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

use MMNewmedia\Xsd;

#[Xsd\ComplexType]
#[Xsd\Schema(
    targetNamespace: 'urn:example:invoice',
    xmlns: 'urn:example:invoice',
)]
#[Xsd\RootElement(namespace: Namespaces::INV, name: 'TestPattern', type: TestPatternType::class)]
final readonly class TestPatternType
{
    public function __construct(
        #[Xsd\Element(namespace: Namespaces::CBC, name: 'Pattern', type: PatternType::class)]
        #[Xsd\Sequence(position: 1)]
        public PatternType $pattern,
        #[Xsd\Element(namespace: Namespaces::CBC, name: 'MultiPattern', type: MultiPatternType::class)]
        #[Xsd\Sequence(position: 2)]
        public MultiPatternType $multiPattern,
    ) {}
}
