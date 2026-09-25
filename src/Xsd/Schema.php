<?php

declare(strict_types=1);

namespace MMNewmedia\Xsd;

use Attribute;
use BackedEnum;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Schema
{
    public ?string $xmlns;
    public ?string $targetNamespace;
    public ?string $elementFormDefault;
    public ?string $attributeFormDefault;
    public ?string $version;

    public function __construct(
        BackedEnum|string|null $xmlns = null,
        BackedEnum|string|null $targetNamespace = null,
        ?string $elementFormDefault = null,
        ?string $attributeFormDefault = null,
        ?string $version = null,
    ) {
        $this->xmlns = $xmlns instanceof BackedEnum ? $xmlns->value : $xmlns;
        $this->targetNamespace = $targetNamespace instanceof BackedEnum ? $targetNamespace->value : $targetNamespace;
        $this->elementFormDefault = $elementFormDefault;
        $this->attributeFormDefault = $attributeFormDefault;
        $this->version = $version;
    }
}
