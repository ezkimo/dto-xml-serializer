<?php

declare(strict_types=1);

namespace MMNewmedia\Xsd;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Sequence
{
    public function __construct(
        public ?string $id = null,
        public ?int $maxOccurs = null,
        public ?int $minOccurs = null,
        public ?int $position = null,
    ) {}
}