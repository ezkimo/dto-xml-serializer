<?php

declare(strict_types=1);

namespace MMNewmedia\Xsd;
use Attribute as GlobalAttribute;

#[GlobalAttribute(GlobalAttribute::TARGET_PROPERTY)]
final readonly class Attribute
{
    public function __construct(
        public ?string $default = null,
        public ?string $fixed = null,
        public ?AttributeFormType $form = null,
        public ?string $id = null,
        public ?string $name = null,
        public ?string $ref = null,
        public ?string $type = null,
        public ?AttributeUseType $use = null,
    ) {}
}