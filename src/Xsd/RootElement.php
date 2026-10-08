<?php

declare(strict_types=1);

namespace MMNewmedia\Xsd;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class RootElement extends Element
{
}