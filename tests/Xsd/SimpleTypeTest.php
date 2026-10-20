<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Xsd;

use MMNewmedia\Xsd\SimpleType;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class SimpleTypeTest extends TestCase
{
    public function testConstructorAssignsAllProperties(): void
    {
        $simpleType = new SimpleType(name: 'TestType', id: 'test-id', final: '#all');

        self::assertSame('TestType', $simpleType->name);
        self::assertSame('test-id', $simpleType->id);
        self::assertSame('#all', $simpleType->final);
    }

    public function testDefaultsToNull(): void
    {
        $simpleType = new SimpleType();

        self::assertNull($simpleType->name);
        self::assertNull($simpleType->id);
        self::assertNull($simpleType->final);
    }

    public function testAttributeTargetsClassAndProperty(): void
    {
        $reflector = new ReflectionClass(SimpleType::class);
        $attributes = $reflector->getAttributes();

        self::assertNotEmpty($attributes);
    }
}
