<?php

declare(strict_types=1);

namespace MMNewmedia\Xsd;

use Attribute;
use BackedEnum;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_CLASS)]
final readonly class Element
{
    public function __construct(
        public ?bool $abstract = null,
        public ?string $block = null,
        public ?string $default = null,
        public ?string $final = null,
        public ?string $fixed = null,
        public ?string $id = null,
        public ?int $maxOccurs = null,
        public ?int $minOccurs = null,
        public ?BackedEnum $namespace = null,
        public ?string $name = null,
        public ?bool $nillable = null,
        public ?string $ref = null,
        public ?string $type = null,
    ) {}
}