<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Xsd;

use Attribute;
use MMNewmedia\Xsd\MaxInclusive;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class MaxInclusiveTest extends TestCase
{
    public function testConstructorAssignsAllProperties(): void
    {
        $maxInclusive = new MaxInclusive(value: '100.00', id: 'max-inclusive-id');

        self::assertSame('100.00', $maxInclusive->value);
        self::assertSame('max-inclusive-id', $maxInclusive->id);
    }

    public function testIdDefaultsToNull(): void
    {
        $maxInclusive = new MaxInclusive('100.00');

        self::assertSame('100.00', $maxInclusive->value);
        self::assertNull($maxInclusive->id);
    }

    public function testConstructorAcceptsIntegerFloatAndStringValues(): void
    {
        self::assertSame(100, (new MaxInclusive(100))->value);
        self::assertSame(100.5, (new MaxInclusive(100.5))->value);
        self::assertSame('100.50', (new MaxInclusive('100.50'))->value);
    }

    public function testAttributeTargetsClassAndProperty(): void
    {
        $attribute = (new ReflectionClass(MaxInclusive::class))->getAttributes(Attribute::class)[0] ?? null;

        self::assertNotNull($attribute);
        self::assertSame(
            Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY,
            $attribute->newInstance()->flags
        );
    }
}
