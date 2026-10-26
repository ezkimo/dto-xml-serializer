<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Xsd;

use Attribute;
use MMNewmedia\Xsd\FractionDigits;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class FractionDigitsTest extends TestCase
{
    public function testConstructorAssignsAllProperties(): void
    {
        $fractionDigits = new FractionDigits(value: 2, id: 'fraction-digits-id');

        self::assertSame(2, $fractionDigits->value);
        self::assertSame('fraction-digits-id', $fractionDigits->id);
    }

    public function testIdDefaultsToNull(): void
    {
        $fractionDigits = new FractionDigits(2);

        self::assertSame(2, $fractionDigits->value);
        self::assertNull($fractionDigits->id);
    }

    public function testAttributeTargetsClassAndProperty(): void
    {
        $attribute = (new ReflectionClass(FractionDigits::class))->getAttributes(Attribute::class)[0] ?? null;

        self::assertNotNull($attribute);
        self::assertSame(
            Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY,
            $attribute->newInstance()->flags
        );
    }
}
