<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Xsd;

use MMNewmedia\Xsd\Restriction;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class RestrictionTest extends TestCase
{
    public function testConstructorAssignsAllProperties(): void
    {
        $restriction = new Restriction(base: 'xsd:string', id: 'restriction-id');

        self::assertSame('xsd:string', $restriction->base);
        self::assertSame('restriction-id', $restriction->id);
    }

    public function testDefaultsToNull(): void
    {
        $restriction = new Restriction();

        self::assertNull($restriction->base);
        self::assertNull($restriction->id);
    }

    public function testAttributeTargetsClassAndProperty(): void
    {
        $reflector = new ReflectionClass(Restriction::class);
        $attributes = $reflector->getAttributes();

        self::assertNotEmpty($attributes);
    }
}
