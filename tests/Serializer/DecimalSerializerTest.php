<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Serializer;

use MMNewmedia\Serializer\JsonDtoSerializer;
use MMNewmedia\Serializer\XmlDtoSerializer;
use MMNewmedia\Tests\Fixtures\CombinedDecimalType;
use MMNewmedia\Tests\Fixtures\FractionDigitsType;
use MMNewmedia\Tests\Fixtures\InheritedDecimalType;
use MMNewmedia\Tests\Fixtures\MaxInclusiveType;
use MMNewmedia\Tests\Fixtures\MinExclusiveType;
use MMNewmedia\Tests\Fixtures\Namespaces;
use MMNewmedia\Tests\Fixtures\PreciseMaxInclusiveType;
use MMNewmedia\Tests\Fixtures\TestDecimalType;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DecimalSerializerTest extends TestCase
{
    private XmlDtoSerializer $xmlSerializer;
    private JsonDtoSerializer $jsonSerializer;

    #[\Override]
    protected function setUp(): void
    {
        $this->xmlSerializer = new XmlDtoSerializer();
        $this->xmlSerializer->setNamespaces(Namespaces::class);

        $this->jsonSerializer = new JsonDtoSerializer();
        $this->jsonSerializer->setRootClasses(['TestDecimal' => TestDecimalType::class]);
    }

    public function testXmlSerializeAcceptsValuesWithinFacets(): void
    {
        $xml = $this->xmlSerializer->serialize(self::dto());

        self::assertStringContainsString('<inv:FractionDigits>12.34</inv:FractionDigits>', $xml);
        self::assertStringContainsString('<inv:MaxInclusive>100.00</inv:MaxInclusive>', $xml);
        self::assertStringContainsString('<inv:MinExclusive>0.01</inv:MinExclusive>', $xml);
        self::assertStringContainsString('<inv:InheritedDecimal>49.99</inv:InheritedDecimal>', $xml);
        self::assertStringContainsString('<inv:Combined>999.99</inv:Combined>', $xml);
        self::assertStringContainsString(
            '<inv:PreciseMaxInclusive>9007199254740993</inv:PreciseMaxInclusive>',
            $xml
        );
    }

    public function testXmlSerializeAcceptsTrailingZerosForFractionDigits(): void
    {
        $xml = $this->xmlSerializer->serialize(self::dto(
            fractionDigits: '1.2300',
            combined: '999.9900',
        ));

        self::assertStringContainsString('<inv:FractionDigits>1.2300</inv:FractionDigits>', $xml);
        self::assertStringContainsString('<inv:Combined>999.9900</inv:Combined>', $xml);
    }

    public function testXmlSerializeAcceptsValuesAboveMinExclusive(): void
    {
        $xml = $this->xmlSerializer->serialize(self::dto(minExclusive: '0.0001'));

        self::assertStringContainsString('<inv:MinExclusive>0.0001</inv:MinExclusive>', $xml);
    }

    public function testXmlSerializeAcceptsLexicalDecimalWithoutLeadingZero(): void
    {
        $xml = $this->xmlSerializer->serialize(self::dto(fractionDigits: '.5'));

        self::assertStringContainsString('<inv:FractionDigits>.5</inv:FractionDigits>', $xml);
    }

    public function testXmlSerializeAcceptsTrailingDecimalPoint(): void
    {
        $xml = $this->xmlSerializer->serialize(self::dto(fractionDigits: '1.'));

        self::assertStringContainsString('<inv:FractionDigits>1.</inv:FractionDigits>', $xml);
    }

    public function testXmlSerializeRejectsNegativeZeroForMinExclusive(): void
    {
        $this->assertXmlRejects(
            "Value '-0.000' is not greater than minExclusive 0.",
            self::dto(minExclusive: '-0.000')
        );
    }

    public function testXmlSerializeHandlesExponentNotationFromFloatValues(): void
    {
        $value = 1.0e-7;

        $this->assertXmlRejects(
            sprintf("Value '%s' has more than 2 fraction digits (7).", strval($value)),
            self::dto(fractionDigits: $value)
        );
    }

    public function testXmlSerializeRejectsTooManyFractionDigits(): void
    {
        $this->assertXmlRejects(
            "Value '12.345' has more than 2 fraction digits (3).",
            self::dto(fractionDigits: '12.345')
        );
    }

    public function testXmlSerializeRejectsValueAboveMaxInclusive(): void
    {
        $this->assertXmlRejects(
            "Value '100.01' is greater than maxInclusive 100.00.",
            self::dto(maxInclusive: '100.01')
        );
    }

    public function testXmlSerializeRejectsValueEqualToMinExclusive(): void
    {
        $this->assertXmlRejects(
            "Value '0' is not greater than minExclusive 0.",
            self::dto(minExclusive: '0')
        );
    }

    public function testXmlSerializeRejectsValueBelowMinExclusive(): void
    {
        $this->assertXmlRejects(
            "Value '-1' is not greater than minExclusive 0.",
            self::dto(minExclusive: '-1')
        );
    }

    public function testXmlSerializeUsesStrictestInheritedFractionDigits(): void
    {
        $this->assertXmlRejects(
            "Value '1.234' has more than 2 fraction digits (3).",
            self::dto(inheritedDecimal: '1.234')
        );
    }

    public function testXmlSerializeUsesStrictestInheritedMaxInclusive(): void
    {
        $this->assertXmlRejects(
            "Value '50.01' is greater than maxInclusive 50.00.",
            self::dto(inheritedDecimal: '50.01')
        );
    }

    public function testXmlSerializeUsesStrictestInheritedMinExclusive(): void
    {
        $this->assertXmlRejects(
            "Value '-5' is not greater than minExclusive -5.",
            self::dto(inheritedDecimal: '-5')
        );
    }

    public function testXmlSerializeRejectsNonDecimalValue(): void
    {
        $this->assertXmlRejects(
            "Value 'abc' is not a valid decimal.",
            self::dto(fractionDigits: 'abc')
        );
    }

    public function testXmlSerializeComparesDecimalsExactlyBeyondFloatPrecision(): void
    {
        $this->assertXmlRejects(
            "Value '9007199254740994' is greater than maxInclusive 9007199254740993.",
            self::dto(preciseMaxInclusive: '9007199254740994')
        );
    }

    public function testJsonSerializeAcceptsValuesWithinFacets(): void
    {
        $data = json_decode($this->jsonSerializer->serialize(self::dto()), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(
            [
                'TestDecimal' => [
                    'FractionDigits' => '12.34',
                    'MaxInclusive' => '100.00',
                    'MinExclusive' => '0.01',
                    'InheritedDecimal' => '49.99',
                    'Combined' => '999.99',
                    'PreciseMaxInclusive' => '9007199254740993',
                ],
            ],
            $data
        );
    }

    public function testJsonSerializeAcceptsTrailingZerosForFractionDigits(): void
    {
        $data = json_decode(
            $this->jsonSerializer->serialize(self::dto(
                fractionDigits: '1.2300',
                combined: '999.9900',
            )),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertSame('1.2300', $data['TestDecimal']['FractionDigits']);
        self::assertSame('999.9900', $data['TestDecimal']['Combined']);
    }

    public function testJsonSerializeRejectsTooManyFractionDigits(): void
    {
        $this->assertJsonSerializeRejects(
            "Value '12.345' has more than 2 fraction digits (3).",
            self::dto(fractionDigits: '12.345')
        );
    }

    public function testJsonSerializeRejectsValueAboveMaxInclusive(): void
    {
        $this->assertJsonSerializeRejects(
            "Value '100.01' is greater than maxInclusive 100.00.",
            self::dto(maxInclusive: '100.01')
        );
    }

    public function testJsonSerializeRejectsValueEqualToMinExclusive(): void
    {
        $this->assertJsonSerializeRejects(
            "Value '0' is not greater than minExclusive 0.",
            self::dto(minExclusive: '0')
        );
    }

    public function testJsonSerializeRejectsNonDecimalValue(): void
    {
        $this->assertJsonSerializeRejects(
            "Value 'abc' is not a valid decimal.",
            self::dto(fractionDigits: 'abc')
        );
    }

    public function testJsonSerializeComparesDecimalsExactlyBeyondFloatPrecision(): void
    {
        $this->assertJsonSerializeRejects(
            "Value '9007199254740994' is greater than maxInclusive 9007199254740993.",
            self::dto(preciseMaxInclusive: '9007199254740994')
        );
    }

    public function testJsonUnserializeAcceptsValuesWithinFacets(): void
    {
        $dto = $this->jsonSerializer->unserialize(self::json());

        self::assertInstanceOf(TestDecimalType::class, $dto);
        self::assertSame('12.34', $dto->fractionDigits->getValue());
        self::assertSame('100.00', $dto->maxInclusive->getValue());
        self::assertSame('0.01', $dto->minExclusive->getValue());
        self::assertSame('49.99', $dto->inheritedDecimal->getValue());
        self::assertSame('999.99', $dto->combined->getValue());
        self::assertSame('9007199254740993', $dto->preciseMaxInclusive->getValue());
    }

    public function testJsonUnserializeAcceptsTrailingZerosForFractionDigits(): void
    {
        $dto = $this->jsonSerializer->unserialize(self::json(
            fractionDigits: '1.2300',
            combined: '999.9900',
        ));

        self::assertInstanceOf(TestDecimalType::class, $dto);
        self::assertSame('1.2300', $dto->fractionDigits->getValue());
        self::assertSame('999.9900', $dto->combined->getValue());
    }

    public function testJsonUnserializeRejectsTooManyFractionDigits(): void
    {
        $this->assertJsonUnserializeRejects(
            "Value '12.345' has more than 2 fraction digits (3).",
            self::json(fractionDigits: '12.345')
        );
    }

    public function testJsonUnserializeRejectsValueAboveMaxInclusive(): void
    {
        $this->assertJsonUnserializeRejects(
            "Value '100.01' is greater than maxInclusive 100.00.",
            self::json(maxInclusive: '100.01')
        );
    }

    public function testJsonUnserializeRejectsValueEqualToMinExclusive(): void
    {
        $this->assertJsonUnserializeRejects(
            "Value '0' is not greater than minExclusive 0.",
            self::json(minExclusive: '0')
        );
    }

    public function testJsonUnserializeRejectsNonDecimalValue(): void
    {
        $this->assertJsonUnserializeRejects(
            "Value 'abc' is not a valid decimal.",
            self::json(fractionDigits: 'abc')
        );
    }

    public function testJsonUnserializeComparesDecimalsExactlyBeyondFloatPrecision(): void
    {
        $this->assertJsonUnserializeRejects(
            "Value '9007199254740994' is greater than maxInclusive 9007199254740993.",
            self::json(preciseMaxInclusive: '9007199254740994')
        );
    }

    public function testJsonUnserializeUsesStrictestInheritedMaxInclusive(): void
    {
        $this->assertJsonUnserializeRejects(
            "Value '50.01' is greater than maxInclusive 50.00.",
            self::json(inheritedDecimal: '50.01')
        );
    }

    private function assertXmlRejects(string $message, TestDecimalType $dto): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($message);

        $this->xmlSerializer->serialize($dto);
    }

    private function assertJsonSerializeRejects(string $message, TestDecimalType $dto): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($message);

        $this->jsonSerializer->serialize($dto);
    }

    private function assertJsonUnserializeRejects(string $message, string $json): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($message);

        $this->jsonSerializer->unserialize($json);
    }

    private static function dto(
        string|int|float $fractionDigits = '12.34',
        string|int|float $maxInclusive = '100.00',
        string|int|float $minExclusive = '0.01',
        string|int|float $inheritedDecimal = '49.99',
        string|int|float $combined = '999.99',
        string|int|float $preciseMaxInclusive = '9007199254740993',
    ): TestDecimalType {
        $fractionDigitsType = new FractionDigitsType();
        $fractionDigitsType->setValue($fractionDigits);

        $maxInclusiveType = new MaxInclusiveType();
        $maxInclusiveType->setValue($maxInclusive);

        $minExclusiveType = new MinExclusiveType();
        $minExclusiveType->setValue($minExclusive);

        $inheritedDecimalType = new InheritedDecimalType();
        $inheritedDecimalType->setValue($inheritedDecimal);

        $combinedType = new CombinedDecimalType();
        $combinedType->setValue($combined);

        $preciseMaxInclusiveType = new PreciseMaxInclusiveType();
        $preciseMaxInclusiveType->setValue($preciseMaxInclusive);

        return new TestDecimalType(
            $fractionDigitsType,
            $maxInclusiveType,
            $minExclusiveType,
            $inheritedDecimalType,
            $combinedType,
            $preciseMaxInclusiveType,
        );
    }

    private static function json(
        string $fractionDigits = '12.34',
        string $maxInclusive = '100.00',
        string $minExclusive = '0.01',
        string $inheritedDecimal = '49.99',
        string $combined = '999.99',
        string $preciseMaxInclusive = '9007199254740993',
    ): string {
        return json_encode(
            [
                'TestDecimal' => [
                    'FractionDigits' => $fractionDigits,
                    'MaxInclusive' => $maxInclusive,
                    'MinExclusive' => $minExclusive,
                    'InheritedDecimal' => $inheritedDecimal,
                    'Combined' => $combined,
                    'PreciseMaxInclusive' => $preciseMaxInclusive,
                ],
            ],
            JSON_THROW_ON_ERROR
        );
    }
}
