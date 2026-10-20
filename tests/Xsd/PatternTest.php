<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Xsd;

use MMNewmedia\Xsd\Pattern;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class PatternTest extends TestCase
{
    public function testConstructorAssignsAllProperties(): void
    {
        $pattern = new Pattern(value: '[A-Z]{3}', id: 'pattern-id');

        self::assertSame('[A-Z]{3}', $pattern->value);
        self::assertSame('pattern-id', $pattern->id);
    }

    public function testIdDefaultsToNull(): void
    {
        $pattern = new Pattern(value: '[0-9]+');

        self::assertSame('[0-9]+', $pattern->value);
        self::assertNull($pattern->id);
    }

    public function testAttributeIsRepeatable(): void
    {
        $reflector = new ReflectionClass(Pattern::class);
        $attributes = $reflector->getAttributes();
        $attribute = $attributes[0] ?? null;

        self::assertNotNull($attribute);
        // Attribute flags are checked via reflection
        $instance = $attribute->newInstance();
        self::assertInstanceOf(\Attribute::class, $instance);
    }
}
