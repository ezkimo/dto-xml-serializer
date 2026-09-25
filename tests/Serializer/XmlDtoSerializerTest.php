<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Serializer;

use MMNewmedia\Serializer\XmlDtoSerializer;
use MMNewmedia\Tests\Fixtures\Namespaces;
use PHPUnit\Framework\TestCase;

use function strpos;

final class XmlDtoSerializerTest extends TestCase
{
    private XmlDtoSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new XmlDtoSerializer();
        $this->serializer->setNamespaces(Namespaces::class);
    }

    public function testSerializeRootElementAndNamespaceDeclarations(): void
    {
        $xml = $this->serializer->serialize(JsonDtoSerializerTest::invoice());

        self::assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $xml);
        self::assertStringContainsString('<inv:TestInvoice', $xml);
        self::assertStringContainsString('xmlns:inv="urn:example:invoice"', $xml);
        self::assertStringContainsString('xmlns:cbc="urn:example:commonbasic"', $xml);
        self::assertStringContainsString('xmlns:cac="urn:example:commonaggregate"', $xml);
        self::assertStringContainsString('xmlns:udt="urn:example:unqualifieddata"', $xml);
        self::assertStringContainsString('xmlns:ext="urn:example:extension"', $xml);
    }

    public function testSerializeChildrenInheritEnclosingTypesTargetNamespace(): void
    {
        $xml = $this->serializer->serialize(JsonDtoSerializerTest::invoice());

        self::assertStringContainsString('<inv:ID>RE-42</inv:ID>', $xml);
        self::assertStringContainsString('<inv:IssueDate>2025-08-20</inv:IssueDate>', $xml);
        self::assertStringContainsString('<inv:CopyIndicator>true</inv:CopyIndicator>', $xml);
        self::assertStringContainsString('<inv:PayableAmount currencyID="EUR">199.90</inv:PayableAmount>', $xml);
        self::assertStringContainsString('<inv:InvoiceTypeCode listID="100">380</inv:InvoiceTypeCode>', $xml);
        self::assertStringContainsString('<inv:BuyerParty>', $xml);
        self::assertStringContainsString('<cac:PartyName>Acme GmbH</cac:PartyName>', $xml);
        self::assertStringContainsString('<cac:PartyRole listID="305">Buyer</cac:PartyRole>', $xml);
    }

    public function testSerializeCollectionsRepeatsElements(): void
    {
        $xml = $this->serializer->serialize(JsonDtoSerializerTest::invoice());

        self::assertSame(2, substr_count($xml, '<inv:Note>'));
        self::assertStringContainsString('<inv:Note>first note</inv:Note>', $xml);
        self::assertStringContainsString('<inv:Note>second note</inv:Note>', $xml);
        self::assertSame(2, substr_count($xml, '<inv:AdditionalParty>'));
        self::assertStringContainsString('<cac:PartyRole listID="407">Agent</cac:PartyRole>', $xml);
        self::assertStringContainsString('<cac:PartyRole listID="408">Sub</cac:PartyRole>', $xml);
    }

    public function testSerializeRespectsSequencePositions(): void
    {
        $xml = $this->serializer->serialize(JsonDtoSerializerTest::invoice());

        $positions = array_map(
            fn(string $needle): int => strpos($xml, $needle),
            ['<inv:ID', '<inv:IssueDate', '<inv:CopyIndicator', '<inv:PayableAmount', '<inv:InvoiceTypeCode', '<inv:BuyerParty', '<inv:Note', '<inv:AdditionalParty']
        );

        self::assertSame($positions, (function (array $values): array {
            $sorted = $values;
            sort($sorted);

            return $sorted;
        })($positions));
    }

    public function testUsePropertyNamespacesGivesElementNamespacesPrecedence(): void
    {
        $this->serializer->setUsePropertyNamespaces(true);
        $xml = $this->serializer->serialize(JsonDtoSerializerTest::invoice());

        self::assertStringContainsString('<cbc:ID>RE-42</cbc:ID>', $xml);
        self::assertStringContainsString('<cac:BuyerParty>', $xml);
        self::assertStringContainsString('<cbc:PartyName>Acme GmbH</cbc:PartyName>', $xml);
        self::assertStringContainsString('<cbc:PartyRole listID="305">Buyer</cbc:PartyRole>', $xml);
        self::assertStringContainsString('<cbc:Note>first note</cbc:Note>', $xml);
        self::assertStringContainsString('<cac:AdditionalParty>', $xml);
    }

    public function testSerializeRequiresElementAttributeOnRoot(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Missing #[Element] attribute');

        $serializer = new XmlDtoSerializer();
        $serializer->serialize(new \stdClass());
    }
}