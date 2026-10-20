<?php

declare(strict_types=1);

namespace MMNewmedia\Xsd;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
final readonly class SimpleType
{
    public function __construct(
        public ?string $name = null,
        public ?string $id = null,
        public ?string $final = null,
    ) {}
}
