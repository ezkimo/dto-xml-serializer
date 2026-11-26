<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Xsd;

use Attribute;
use MMNewmedia\Xsd\Choice;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionObject;

final class ChoiceTest extends TestCase
{
    public function testConstructorAssignsAllProperties(): void
    {
        $choice = new Choice(
            group: 'payment',
            id: 'choice-id',
            maxOccurs: 1,
            minOccurs: 1,
        );

        self::assertSame('payment', $choice->group);
        self::assertSame('choice-id', $choice->id);
        self::assertSame(1, $choice->maxOccurs);
        self::assertSame(1, $choice->minOccurs);
    }

    public function testArgumentsDefaultToNull(): void
    {
        $choice = new Choice();

        self::assertNull($choice->group);
        self::assertNull($choice->id);
        self::assertNull($choice->maxOccurs);
        self::assertNull($choice->minOccurs);
    }

    public function testAttributeTargetsProperty(): void
    {
        $attribute = (new ReflectionClass(Choice::class))->getAttributes(Attribute::class)[0] ?? null;

        self::assertNotNull($attribute);
        self::assertSame(
            Attribute::TARGET_PROPERTY,
            $attribute->newInstance()->flags
        );
    }

    public function testIsUsableOnProperty(): void
    {
        $object = new class {
            #[Choice(group: 'payment', minOccurs: 1)]
            public ?string $bankCard = null;
        };

        $property = (new ReflectionObject($object))->getProperty('bankCard');
        $attribute = $property->getAttributes(Choice::class)[0] ?? null;

        self::assertNotNull($attribute);

        $choice = $attribute->newInstance();
        self::assertSame('payment', $choice->group);
        self::assertSame(1, $choice->minOccurs);
    }
}
