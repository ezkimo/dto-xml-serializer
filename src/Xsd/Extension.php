<?php

declare(strict_types=1);

namespace MMNewmedia\Xsd;

use Attribute;

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