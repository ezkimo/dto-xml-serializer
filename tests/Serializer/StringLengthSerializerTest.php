<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Serializer;

use MMNewmedia\Serializer\JsonDtoSerializer;
use MMNewmedia\Serializer\XmlDtoSerializer;
use MMNewmedia\Tests\Fixtures\InheritedLengthType;
use MMNewmedia\Tests\Fixtures\MaxLengthType;
use MMNewmedia\Tests\Fixtures\MinLengthType;
use MMNewmedia\Tests\Fixtures\Namespaces;
use MMNewmedia\Tests\Fixtures\RangeLengthType;
use MMNewmedia\Tests\Fixtures\TestStringLengthType;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class StringLengthSerializerTest extends TestCase
{
    private XmlDtoSerializer $xmlSerializer;
    private JsonDtoSerializer $jsonSerializer;

    #[\Override]
    protected function setUp(): void
    {
        $this->xmlSerializer = new XmlDtoSerializer();
        $this->xmlSerializer->setNamespaces(Namespaces::class);

        $this->jsonSerializer = new JsonDtoSerializer();
        $this->jsonSerializer->setRootClasses(['TestStringLength' => TestStringLengthType::class]);
    }

    public function testXmlSerializeAcceptsValuesWithinBounds(): void
    {
        $xml = $this->xmlSerializer->serialize(self::dto());

        self::assertStringContainsString('<inv:MinLength>ABC</inv:MinLength>', $xml);
        self::assertStringContainsString('<inv:MaxLength>ABCDE</inv:MaxLength>', $xml);
        self::assertStringContainsString('<inv:RangeLength>AB</inv:RangeLength>', $xml);
        self::assertStringContainsString('<inv:InheritedLength>ABCD</inv:InheritedLength>', $xml);
    }

    public function testXmlSerializeAcceptsBoundaryValues(): void
    {
        $xml = $this->xmlSerializer->serialize(self::dto(
            min: 'ABC',
            max: 'ABCDE',
            range: 'ABCD',
            inherited: 'ABCDEF',
        ));

        self::assertStringContainsString('<inv:RangeLength>ABCD</inv:RangeLength>', $xml);
        self::assertStringContainsString('<inv:InheritedLength>ABCDEF</inv:InheritedLength>', $xml);
    }

    public function testXmlSerializeRejectsValueBelowMinLength(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Value 'AB' is shorter than minLength 3 (length 2).");

        $this->xmlSerializer->serialize(self::dto(min: 'AB'));
    }

    public function testXmlSerializeRejectsValueAboveMaxLength(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Value 'ABCDEF' is longer than maxLength 5 (length 6).");

        $this->xmlSerializer->serialize(self::dto(max: 'ABCDEF'));
    }

    public function testXmlSerializeRejectsValueBelowRangeMinLength(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Value 'A' is shorter than minLength 2 (length 1).");

        $this->xmlSerializer->serialize(self::dto(range: 'A'));
    }

    public function testXmlSerializeRejectsValueAboveRangeMaxLength(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Value 'ABCDE' is longer than maxLength 4 (length 5).");

        $this->xmlSerializer->serialize(self::dto(range: 'ABCDE'));
    }

    public function testXmlSerializeCountsMultibyteCharactersForMaxLength(): void
    {
        $xml = $this->xmlSerializer->serialize(self::dto(range: 'äöü'));

        self::assertStringContainsString('<inv:RangeLength>äöü</inv:RangeLength>', $xml);
    }

    public function testXmlSerializeCountsMultibyteCharactersForMinLength(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Value 'äö' is shorter than minLength 3 (length 2).");

        $this->xmlSerializer->serialize(self::dto(min: 'äö'));
    }

    public function testXmlSerializeUsesStrictestInheritedMinLength(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Value 'ABC' is shorter than minLength 4 (length 3).");

        $this->xmlSerializer->serialize(self::dto(inherited: 'ABC'));
    }

    public function testXmlSerializeUsesStrictestInheritedMaxLength(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Value 'ABCDEFG' is longer than maxLength 6 (length 7).");

        $this->xmlSerializer->serialize(self::dto(inherited: 'ABCDEFG'));
    }

    public function testJsonSerializeAcceptsValuesWithinBounds(): void
    {
        $data = json_decode($this->jsonSerializer->serialize(self::dto()), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(
            [
                'TestStringLength' => [
                    'MinLength' => 'ABC',
                    'MaxLength' => 'ABCDE',
                    'RangeLength' => 'AB',
                    'InheritedLength' => 'ABCD',
                ],
            ],
            $data
        );
    }

    public function testJsonSerializeRejectsValueBelowMinLength(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Value 'AB' is shorter than minLength 3 (length 2).");

        $this->jsonSerializer->serialize(self::dto(min: 'AB'));
    }

    public function testJsonSerializeRejectsValueAboveMaxLength(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Value 'ABCDEF' is longer than maxLength 5 (length 6).");

        $this->jsonSerializer->serialize(self::dto(max: 'ABCDEF'));
    }

    public function testJsonSerializeUsesStrictestInheritedMaxLength(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Value 'ABCDEFG' is longer than maxLength 6 (length 7).");

        $this->jsonSerializer->serialize(self::dto(inherited: 'ABCDEFG'));
    }

    public function testJsonUnserializeAcceptsValuesWithinBounds(): void
    {
        $dto = $this->jsonSerializer->unserialize(self::json());

        self::assertInstanceOf(TestStringLengthType::class, $dto);
        self::assertSame('ABC', $dto->minLength->getValue());
        self::assertSame('ABCDE', $dto->maxLength->getValue());
        self::assertSame('AB', $dto->rangeLength->getValue());
        self::assertSame('ABCD', $dto->inheritedLength->getValue());
    }

    public function testJsonUnserializeRejectsValueBelowMinLength(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Value 'AB' is shorter than minLength 3 (length 2).");

        $this->jsonSerializer->unserialize(self::json(min: 'AB'));
    }

    public function testJsonUnserializeRejectsValueAboveMaxLength(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Value 'ABCDEF' is longer than maxLength 5 (length 6).");

        $this->jsonSerializer->unserialize(self::json(max: 'ABCDEF'));
    }

    public function testJsonUnserializeAcceptsMultibyteValueWithinMaxLength(): void
    {
        $dto = $this->jsonSerializer->unserialize(self::json(range: 'äöü'));

        self::assertInstanceOf(TestStringLengthType::class, $dto);
        self::assertSame('äöü', $dto->rangeLength->getValue());
    }

    public function testJsonUnserializeUsesStrictestInheritedMinLength(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Value 'ABC' is shorter than minLength 4 (length 3).");

        $this->jsonSerializer->unserialize(self::json(inherited: 'ABC'));
    }

    private static function dto(
        string $min = 'ABC',
        string $max = 'ABCDE',
        string $range = 'AB',
        string $inherited = 'ABCD',
    ): TestStringLengthType {
        $minLength = new MinLengthType();
        $minLength->setValue($min);

        $maxLength = new MaxLengthType();
        $maxLength->setValue($max);

        $rangeLength = new RangeLengthType();
        $rangeLength->setValue($range);

        $inheritedLength = new InheritedLengthType();
        $inheritedLength->setValue($inherited);

        return new TestStringLengthType($minLength, $maxLength, $rangeLength, $inheritedLength);
    }

    private static function json(
        string $min = 'ABC',
        string $max = 'ABCDE',
        string $range = 'AB',
        string $inherited = 'ABCD',
    ): string {
        return json_encode(
            [
                'TestStringLength' => [
                    'MinLength' => $min,
                    'MaxLength' => $max,
                    'RangeLength' => $range,
                    'InheritedLength' => $inherited,
                ],
            ],
            JSON_THROW_ON_ERROR
        );
    }
}
