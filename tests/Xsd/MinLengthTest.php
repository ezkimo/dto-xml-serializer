<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Xsd;

use Attribute;
use MMNewmedia\Xsd\MinLength;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class MinLengthTest extends TestCase
{
    public function testConstructorAssignsAllProperties(): void
    {
        $minLength = new MinLength(value: 3, id: 'min-length-id');

        self::assertSame(3, $minLength->value);
        self::assertSame('min-length-id', $minLength->id);
    }

    public function testIdDefaultsToNull(): void
    {
        $minLength = new MinLength(3);

        self::assertSame(3, $minLength->value);
        self::assertNull($minLength->id);
    }

    public function testAttributeTargetsClassAndProperty(): void
    {
        $attribute = (new ReflectionClass(MinLength::class))->getAttributes(Attribute::class)[0] ?? null;

        self::assertNotNull($attribute);
        self::assertSame(
            Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY,
            $attribute->newInstance()->flags
        );
    }
}
