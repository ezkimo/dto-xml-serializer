<?php

declare(strict_types=1);

namespace MMNewmedia\Xsd;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
final readonly class MinExclusive
{
    public function __construct(
        public int|float|string $value,
        public ?string $id = null,
    ) {}
}
