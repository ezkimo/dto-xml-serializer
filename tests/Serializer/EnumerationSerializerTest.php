<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Serializer;

use MMNewmedia\Serializer\JsonDtoSerializer;
use MMNewmedia\Serializer\XmlDtoSerializer;
use MMNewmedia\Tests\Fixtures\CurrencyCode;
use MMNewmedia\Tests\Fixtures\CurrencyCodeType;
use MMNewmedia\Tests\Fixtures\Namespaces;
use MMNewmedia\Tests\Fixtures\TestEnumerationType;
use MMNewmedia\Tests\Fixtures\UncheckedCurrencyCodeType;
use PHPUnit\Framework\TestCase;

final class EnumerationSerializerTest extends TestCase
{
    private XmlDtoSerializer $xmlSerializer;
    private JsonDtoSerializer $jsonSerializer;

    #[\Override]
    protected function setUp(): void
    {
        $this->xmlSerializer = new XmlDtoSerializer();
        $this->xmlSerializer->setNamespaces(Namespaces::class);

        $this->jsonSerializer = new JsonDtoSerializer();
        $this->jsonSerializer->setRootClasses(['TestEnumeration' => TestEnumerationType::class]);
    }

    public function testXmlSerializeAcceptsValueOfEnumeration(): void
    {
        $xml = $this->xmlSerializer->serialize(self::dto('EUR'));

        self::assertStringContainsString('<inv:CurrencyCode>EUR</inv:CurrencyCode>', $xml);
        self::assertStringContainsString('<inv:UncheckedCurrencyCode>ANY</inv:UncheckedCurrencyCode>', $xml);
    }

    public function testXmlSerializeRejectsValueOutsideOfEnumeration(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Value 'CHF' is not part of enumeration 'CurrencyCodeType' (EUR, USD, GBP).");

        $this->xmlSerializer->serialize(self::dto('CHF'));
    }

    public function testXmlSerializeAcceptsValueOutsideOfDisabledEnumeration(): void
    {
        $xml = $this->xmlSerializer->serialize(self::dto('EUR', 'CHF'));

        self::assertStringContainsString('<inv:UncheckedCurrencyCode>CHF</inv:UncheckedCurrencyCode>', $xml);
    }

    public function testJsonSerializeAcceptsValueOfEnumeration(): void
    {
        $data = json_decode($this->jsonSerializer->serialize(self::dto('GBP')), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(
            [
                'TestEnumeration' => [
                    'CurrencyCode' => 'GBP',
                    'UncheckedCurrencyCode' => 'ANY',
                ],
            ],
            $data
        );
    }

    public function testJsonSerializeRejectsValueOutsideOfEnumeration(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Value 'CHF' is not part of enumeration 'CurrencyCodeType' (EUR, USD, GBP).");

        $this->jsonSerializer->serialize(self::dto('CHF'));
    }

    public function testJsonUnserializeRejectsValueOutsideOfEnumeration(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Value 'CHF' is not part of enumeration 'CurrencyCodeType' (EUR, USD, GBP).");

        $this->jsonSerializer->unserialize('{"TestEnumeration":{"CurrencyCode":"CHF","UncheckedCurrencyCode":"ANY"}}');
    }

    public function testJsonUnserializeAcceptsValueOutsideOfDisabledEnumeration(): void
    {
        $dto = $this->jsonSerializer->unserialize(
            '{"TestEnumeration":{"CurrencyCode":"EUR","UncheckedCurrencyCode":"CHF"}}'
        );

        self::assertInstanceOf(TestEnumerationType::class, $dto);
        self::assertSame('CHF', $dto->uncheckedCurrencyCode->getValue());
    }

    public function testJsonRoundTripRetainsEnumerationValue(): void
    {
        $dto = $this->jsonSerializer->unserialize($this->jsonSerializer->serialize(self::dto('USD')));

        self::assertInstanceOf(TestEnumerationType::class, $dto);
        self::assertSame(CurrencyCode::USD->value, $dto->currencyCode->getValue());
    }

    private static function dto(string $currency, string $unchecked = 'ANY'): TestEnumerationType
    {
        $currencyCode = new CurrencyCodeType();
        $currencyCode->setValue($currency);

        $uncheckedCode = new UncheckedCurrencyCodeType();
        $uncheckedCode->setValue($unchecked);

        return new TestEnumerationType($currencyCode, $uncheckedCode);
    }
}
