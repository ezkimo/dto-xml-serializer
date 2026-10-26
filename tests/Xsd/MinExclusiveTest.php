<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Xsd;

use Attribute;
use MMNewmedia\Xsd\MinExclusive;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class MinExclusiveTest extends TestCase
{
    public function testConstructorAssignsAllProperties(): void
    {
        $minExclusive = new MinExclusive(value: '0', id: 'min-exclusive-id');

        self::assertSame('0', $minExclusive->value);
        self::assertSame('min-exclusive-id', $minExclusive->id);
    }

    public function testIdDefaultsToNull(): void
    {
        $minExclusive = new MinExclusive('0');

        self::assertSame('0', $minExclusive->value);
        self::assertNull($minExclusive->id);
    }

    public function testConstructorAcceptsIntegerFloatAndStringValues(): void
    {
        self::assertSame(0, (new MinExclusive(0))->value);
        self::assertSame(0.5, (new MinExclusive(0.5))->value);
        self::assertSame('0.50', (new MinExclusive('0.50'))->value);
    }

    public function testAttributeTargetsClassAndProperty(): void
    {
        $attribute = (new ReflectionClass(MinExclusive::class))->getAttributes(Attribute::class)[0] ?? null;

        self::assertNotNull($attribute);
        self::assertSame(
            Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY,
            $attribute->newInstance()->flags
        );
    }
}
