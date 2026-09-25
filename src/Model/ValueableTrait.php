<?php

declare(strict_types=1);

namespace MMNewmedia\Model;

trait ValueableTrait
{
    protected mixed $_;

    public function getValue(): mixed
    {
        return $this->_;
    }

    public function setValue(mixed $value): void
    {
        $this->_ = $value;
    }
}