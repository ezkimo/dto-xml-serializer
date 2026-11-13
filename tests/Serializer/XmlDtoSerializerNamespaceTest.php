<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Serializer;

use InvalidArgumentException;
use LogicException;
use MMNewmedia\Serializer\XmlDtoSerializer;
use MMNewmedia\Tests\Fixtures\DuplicatePrefixNamespaces;
use MMNewmedia\Tests\Fixtures\InvalidUsedNamespaces;
use MMNewmedia\Tests\Fixtures\LegacyMixedCaseNamespaces;
use MMNewmedia\Tests\Fixtures\Namespaces;
use MMNewmedia\Tests\Fixtures\ProblematicNamespaces;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class XmlDtoSerializerNamespaceTest extends TestCase
{
    private const INVOICE = 'urn:example:invoice';
    private const COMMON_AGGREGATE = 'urn:example:commonaggregate';
    private const COMMON_BASIC = 'urn:example:commonbasic';

    public function testExplicitMapWorksWithoutNamespaceEnum(): void
    {
        $serializer = new XmlDtoSerializer();
        $serializer->setNamespacePrefixes([
            self::INVOICE => 'inv',
            self::COMMON_AGGREGATE => 'cac',
        ]);

        $xml = $serializer->serialize(JsonDtoSerializerTest::invoice());

        self::assertStringContainsString('<inv:TestInvoice', $xml);
        self::assertStringContainsString('xmlns:inv="urn:example:invoice"', $xml);
        self::assertStringContainsString('xmlns:cac="urn:example:commonaggregate"', $xml);
        self::assertSame(
            [self::INVOICE => 'inv', self::COMMON_AGGREGATE => 'cac'],
            $serializer->getNamespacePrefixes(),
        );
    }

    public function testExplicitMapOverridesEnumDerivedPrefixes(): void
    {
        $serializer = new XmlDtoSerializer();
        $serializer->setNamespaces(Namespaces::class);
        $serializer->setNamespacePrefixes([
            self::INVOICE => 'bill',
            self::COMMON_AGGREGATE => 'agg',
        ]);

        $xml = $serializer->serialize(JsonDtoSerializerTest::invoice());

        self::assertStringContainsString('<bill:TestInvoice', $xml);
        self::assertStringContainsString('xmlns:bill="urn:example:invoice"', $xml);
        self::assertStringContainsString('xmlns:agg="urn:example:commonaggregate"', $xml);
        self::assertStringNotContainsString('<inv:TestInvoice', $xml);

        // Per-URI fallback: URIs without an explicit entry still use the enum.
        self::assertSame('bill', $serializer->getNamespaceMapping()[self::INVOICE]);
        self::assertSame('CBC', $serializer->getNamespaceMapping()[self::COMMON_BASIC]);
    }

    public function testExplicitPrefixesPreserveCase(): void
    {
        $serializer = new XmlDtoSerializer();
        $serializer->setNamespacePrefixes([
            self::INVOICE => 'UBL',
            self::COMMON_AGGREGATE => 'RAM',
        ]);

        $xml = $serializer->serialize(JsonDtoSerializerTest::invoice());

        self::assertStringContainsString('<UBL:TestInvoice', $xml);
        self::assertStringContainsString('xmlns:UBL="urn:example:invoice"', $xml);
        self::assertStringContainsString('<RAM:PartyName>Acme GmbH</RAM:PartyName>', $xml);
        self::assertStringContainsString('xmlns:RAM="urn:example:commonaggregate"', $xml);
    }

    public function testLegacyEnumDerivedPrefixesAreLowercased(): void
    {
        $serializer = new XmlDtoSerializer();
        $serializer->setNamespaces(LegacyMixedCaseNamespaces::class);

        $xml = $serializer->serialize(JsonDtoSerializerTest::invoice());

        self::assertStringContainsString('<ubl:TestInvoice', $xml);
        self::assertStringContainsString('xmlns:ubl="urn:example:invoice"', $xml);
        self::assertStringContainsString('<ram:PartyName>Acme GmbH</ram:PartyName>', $xml);
        self::assertStringContainsString('xmlns:ram="urn:example:commonaggregate"', $xml);
    }

    public function testUnconfiguredSerializerReturnsEmptyMappings(): void
    {
        $serializer = new XmlDtoSerializer();

        self::assertSame([], $serializer->getNamespaceMapping());
        self::assertSame([], $serializer->getNamespacePrefixes());
    }

    public function testSetNamespacePrefixesResetsCachedMapping(): void
    {
        $serializer = new XmlDtoSerializer();
        $serializer->setNamespacePrefixes([
            self::INVOICE => 'first',
            self::COMMON_AGGREGATE => 'firstagg',
        ]);
        self::assertStringContainsString('<first:TestInvoice', $serializer->serialize(JsonDtoSerializerTest::invoice()));

        $serializer->setNamespacePrefixes([
            self::INVOICE => 'second',
            self::COMMON_AGGREGATE => 'secondagg',
        ]);
        $xml = $serializer->serialize(JsonDtoSerializerTest::invoice());

        self::assertStringContainsString('<second:TestInvoice', $xml);
        self::assertStringNotContainsString('<first:TestInvoice', $xml);
    }

    public function testSetNamespacesResetsCachedMapping(): void
    {
        $serializer = new XmlDtoSerializer();
        $serializer->setNamespaces(Namespaces::class);
        self::assertStringContainsString('<inv:TestInvoice', $serializer->serialize(JsonDtoSerializerTest::invoice()));

        $serializer->setNamespaces(LegacyMixedCaseNamespaces::class);
        $xml = $serializer->serialize(JsonDtoSerializerTest::invoice());

        self::assertStringContainsString('<ubl:TestInvoice', $xml);
        self::assertStringNotContainsString('<inv:TestInvoice', $xml);
    }

    /**
     * @param array<array-key, mixed> $prefixes
     */
    #[DataProvider('invalidPrefixProvider')]
    public function testSetNamespacePrefixesRejectsInvalidEntries(array $prefixes): void
    {
        $serializer = new XmlDtoSerializer();

        $this->expectException(InvalidArgumentException::class);

        $serializer->setNamespacePrefixes($prefixes);
    }

    /**
     * @return iterable<string, array{array<array-key, mixed>}>
     */
    public static function invalidPrefixProvider(): iterable
    {
        yield 'empty prefix' => [[self::INVOICE => '']];
        yield 'leading digit' => [[self::INVOICE => '2ubl']];
        yield 'colon' => [[self::INVOICE => 'ubl:inv']];
        yield 'whitespace' => [[self::INVOICE => 'ub l']];
        yield 'reserved xml' => [[self::INVOICE => 'xml']];
        yield 'reserved xml uppercase' => [[self::INVOICE => 'XML']];
        yield 'reserved xmlns' => [[self::INVOICE => 'xmlns']];
        yield 'empty uri key' => [['' => 'inv']];
        yield 'integer uri key' => [[0 => 'inv']];
        yield 'non-string prefix' => [[self::INVOICE => 123]];
    }

    public function testSetNamespacePrefixesRejectsDuplicatePrefixes(): void
    {
        $serializer = new XmlDtoSerializer();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate XML namespace prefix "same"');
        $this->expectExceptionMessage(self::INVOICE);
        $this->expectExceptionMessage(self::COMMON_AGGREGATE);

        $serializer->setNamespacePrefixes([
            self::INVOICE => 'same',
            self::COMMON_AGGREGATE => 'same',
        ]);
    }

    public function testMissingMappingThrowsDescriptiveException(): void
    {
        $serializer = new XmlDtoSerializer();
        $serializer->setNamespacePrefixes([self::INVOICE => 'inv']);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(self::COMMON_AGGREGATE);
        $this->expectExceptionMessage('PartyName');

        $serializer->serialize(JsonDtoSerializerTest::invoice());
    }

    public function testUnusedInvalidEnumCasesDoNotBreakSerialization(): void
    {
        $serializer = new XmlDtoSerializer();
        $serializer->setNamespaces(ProblematicNamespaces::class);

        $xml = $serializer->serialize(JsonDtoSerializerTest::invoice());

        self::assertStringContainsString('<inv:TestInvoice', $xml);
        self::assertStringNotContainsString('xmlns:2=', $xml);
        self::assertStringNotContainsString('xmlns:xml=', $xml);
        self::assertStringNotContainsString('xmlns:id=', $xml);
        self::assertSame(2, substr_count($xml, 'xmlns:'));
    }

    public function testUsedInvalidEnumPrefixThrowsDescriptiveException(): void
    {
        $serializer = new XmlDtoSerializer();
        $serializer->setNamespaces(InvalidUsedNamespaces::class);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('2010a');
        $this->expectExceptionMessage(self::COMMON_AGGREGATE);

        $serializer->serialize(JsonDtoSerializerTest::invoice());
    }

    public function testUsedEnumPrefixCollisionThrowsDescriptiveException(): void
    {
        $serializer = new XmlDtoSerializer();
        $serializer->setNamespaces(DuplicatePrefixNamespaces::class);
        $serializer->setUsePropertyNamespaces(true);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Duplicate XML namespace prefix "d16a"');
        $this->expectExceptionMessage(self::COMMON_BASIC);
        $this->expectExceptionMessage(self::COMMON_AGGREGATE);

        $serializer->serialize(JsonDtoSerializerTest::invoice());
    }

    public function testNamespacesAreDeclaredOnceOnRootElement(): void
    {
        $serializer = new XmlDtoSerializer();
        $serializer->setNamespacePrefixes([
            self::INVOICE => 'inv',
            self::COMMON_AGGREGATE => 'cac',
        ]);

        $xml = $serializer->serialize(JsonDtoSerializerTest::invoice());

        self::assertSame(2, substr_count($xml, 'xmlns:'));
        self::assertCount(1, array_filter(
            explode("\n", $xml),
            static fn(string $line): bool => str_contains($line, 'xmlns:'),
        ));
    }
}
