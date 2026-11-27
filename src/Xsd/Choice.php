<?php

declare(strict_types=1);

namespace MMNewmedia\Xsd;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Choice
{
    public function __construct(
        public ?string $group = null,
        public ?string $id = null,
        public ?int $maxOccurs = null,
        public ?int $minOccurs = null,
    ) {}
}