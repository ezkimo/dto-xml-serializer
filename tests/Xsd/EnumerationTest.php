<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Xsd;

use MMNewmedia\Tests\Fixtures\CurrencyCode;
use MMNewmedia\Tests\Fixtures\CurrencyCodeType;
use MMNewmedia\Tests\Fixtures\NumericCode;
use MMNewmedia\Xsd\Enumeration;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class EnumerationTest extends TestCase
{
    public function testDerivesValuesFromBackedEnum(): void
    {
        $enumeration = new Enumeration(enum: CurrencyCode::class, name: 'CurrencyCodeType');

        self::assertSame(CurrencyCode::class, $enumeration->enum);
        self::assertSame('CurrencyCodeType', $enumeration->name);
        self::assertSame('xsd:string', $enumeration->base);
        self::assertTrue($enumeration->validate);
        self::assertSame(['EUR', 'USD', 'GBP'], $enumeration->values);
    }

    public function testCastsIntBackedEnumValuesToString(): void
    {
        $enumeration = new Enumeration(enum: NumericCode::class);

        self::assertNull($enumeration->name);
        self::assertSame(['1', '2'], $enumeration->values);
    }

    public function testIsDeclaredOnFixtureClass(): void
    {
        $reflector = new ReflectionClass(CurrencyCodeType::class);
        $attribute = $reflector->getAttributes(Enumeration::class)[0] ?? null;

        self::assertNotNull($attribute);

        $enumeration = $attribute->newInstance();
        self::assertSame(CurrencyCode::class, $enumeration->enum);
        self::assertSame('CurrencyCodeType', $enumeration->name);
        self::assertSame(['EUR', 'USD', 'GBP'], $enumeration->values);
    }
}
