<?php

declare(strict_types=1);

namespace MMNewmedia\Xsd;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class ComplexType
{
    public function __construct(
        public ?bool $abstract = null,
        public ?string $block = null,
        public ?string $final = null,
        public ?string $id = null,
        public ?bool $mixed = null,
        public ?string $name = null,
    ) {}
}