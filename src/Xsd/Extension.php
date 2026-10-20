<?php

declare(strict_types=1);

namespace MMNewmedia\Xsd;

use Attribute;

/**
 * @deprecated Since version 1.x. Use #[Restriction] with #[SimpleType] for simple type restrictions (e.g. with #[Enumeration]) to mirror XSD more accurately. Kept for backward compatibility.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Extension
{
    public function __construct(
        protected string $base,
        protected ?string $id = null,
    ) {}

    public function getBase(): string
    {
        return $this->base;
    }

    public function getId(): ?string
    {
        return $this->id;
    }
}