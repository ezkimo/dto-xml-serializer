<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Xsd;

use Attribute;
use MMNewmedia\Xsd\MaxLength;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class MaxLengthTest extends TestCase
{
    public function testConstructorAssignsAllProperties(): void
    {
        $maxLength = new MaxLength(value: 5, id: 'max-length-id');

        self::assertSame(5, $maxLength->value);
        self::assertSame('max-length-id', $maxLength->id);
    }

    public function testIdDefaultsToNull(): void
    {
        $maxLength = new MaxLength(5);

        self::assertSame(5, $maxLength->value);
        self::assertNull($maxLength->id);
    }

    public function testAttributeTargetsClassAndProperty(): void
    {
        $attribute = (new ReflectionClass(MaxLength::class))->getAttributes(Attribute::class)[0] ?? null;

        self::assertNotNull($attribute);
        self::assertSame(
            Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY,
            $attribute->newInstance()->flags
        );
    }
}
