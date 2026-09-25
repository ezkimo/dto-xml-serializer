<?php

declare(strict_types=1);

namespace MMNewmedia\Model;

interface ValueableInterface
{
    public function getValue(): mixed;

    public function setValue(mixed $value): void;
}