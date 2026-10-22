<?php

declare(strict_types=1);

namespace MMNewmedia\Xsd;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
final readonly class MaxLength
{
    public function __construct(
        public int $value,
        public ?string $id = null,
    ) {}
}
