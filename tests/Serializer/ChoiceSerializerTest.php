<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Serializer;

use MMNewmedia\Serializer\JsonDtoSerializer;
use MMNewmedia\Serializer\XmlDtoSerializer;
use MMNewmedia\Tests\Fixtures\Namespaces;
use MMNewmedia\Tests\Fixtures\TestChoiceType;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ChoiceSerializerTest extends TestCase
{
    private XmlDtoSerializer $xmlSerializer;
    private JsonDtoSerializer $jsonSerializer;

    #[\Override]
    protected function setUp(): void
    {
        $this->xmlSerializer = new XmlDtoSerializer();
        $this->xmlSerializer->setNamespaces(Namespaces::class);

        $this->jsonSerializer = new JsonDtoSerializer();
        $this->jsonSerializer->setRootClasses(['TestChoice' => TestChoiceType::class]);
    }

    public function testXmlSerializeAcceptsOneChoicePerGroup(): void
    {
        $xml = $this->xmlSerializer->serialize(self::dto(bankCard: '4111', voucher: 'SAVE10'));

        self::assertStringContainsString('<inv:BankCard>4111</inv:BankCard>', $xml);
        self::assertStringContainsString('<inv:Voucher>SAVE10</inv:Voucher>', $xml);
        self::assertStringNotContainsString('DirectDebit', $xml);
        self::assertStringNotContainsString('LoyaltyPoints', $xml);
    }

    public function testXmlSerializeAcceptsEmptyOptionalGroup(): void
    {
        $xml = $this->xmlSerializer->serialize(self::dto(bankCard: '4111'));

        self::assertStringContainsString('<inv:BankCard>4111</inv:BankCard>', $xml);
    }

    public function testXmlSerializeRejectsMissingRequiredChoice(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Choice group "payment" requires exactly one value, got 0.');

        $this->xmlSerializer->serialize(self::dto(bankCard: null));
    }

    public function testXmlSerializeRejectsTwoChoicesInRequiredGroup(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Choice group "payment" requires exactly one value, got 2.');

        $this->xmlSerializer->serialize(self::dto(bankCard: '4111', directDebit: 'DE123'));
    }

    public function testXmlSerializeRejectsTwoChoicesInOptionalGroup(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Choice group "discount" allows at most one value, got 2.');

        $this->xmlSerializer->serialize(self::dto(bankCard: '4111', voucher: 'SAVE10', loyaltyPoints: '500'));
    }

    public function testJsonSerializeAcceptsOneChoicePerGroup(): void
    {
        $data = json_decode(
            $this->jsonSerializer->serialize(self::dto(bankCard: '4111', voucher: 'SAVE10')),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertSame(
            ['TestChoice' => ['BankCard' => '4111', 'Voucher' => 'SAVE10']],
            $data
        );
    }

    public function testJsonSerializeRejectsTwoChoicesInRequiredGroup(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Choice group "payment" requires exactly one value, got 2.');

        $this->jsonSerializer->serialize(self::dto(bankCard: '4111', directDebit: 'DE123'));
    }

    public function testJsonUnserializeAcceptsSingleChoice(): void
    {
        $dto = $this->jsonSerializer->unserialize('{"TestChoice":{"BankCard":"4111"}}');

        self::assertInstanceOf(TestChoiceType::class, $dto);
        self::assertSame('4111', $dto->bankCard);
        self::assertNull($dto->directDebit);
    }

    public function testJsonUnserializeRejectsMissingRequiredChoice(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Choice group "payment" requires exactly one value, got 0.');

        $this->jsonSerializer->unserialize('{"TestChoice":{"Voucher":"SAVE10"}}');
    }

    public function testJsonUnserializeRejectsTwoChoicesInRequiredGroup(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Choice group "payment" requires exactly one value, got 2.');

        $this->jsonSerializer->unserialize('{"TestChoice":{"BankCard":"4111","DirectDebit":"DE123"}}');
    }

    private static function dto(
        ?string $bankCard = '4111',
        ?string $directDebit = null,
        ?string $voucher = null,
        ?string $loyaltyPoints = null,
    ): TestChoiceType {
        return new TestChoiceType($bankCard, $directDebit, $voucher, $loyaltyPoints);
    }
}
