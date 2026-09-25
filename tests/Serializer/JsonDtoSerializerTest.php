<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Serializer;

use DateTimeImmutable;
use MMNewmedia\Serializer\JsonDtoSerializer;
use MMNewmedia\Tests\Fixtures\AmountType;
use MMNewmedia\Tests\Fixtures\CodeType;
use MMNewmedia\Tests\Fixtures\DateType;
use MMNewmedia\Tests\Fixtures\IdType;
use MMNewmedia\Tests\Fixtures\IndicatorType;
use MMNewmedia\Tests\Fixtures\NameType;
use MMNewmedia\Tests\Fixtures\NoteType;
use MMNewmedia\Tests\Fixtures\PartyType;
use MMNewmedia\Tests\Fixtures\TestInvoiceType;
use PHPUnit\Framework\TestCase;
use SplObjectStorage;

final class JsonDtoSerializerTest extends TestCase
{
    private JsonDtoSerializer $serializer;

    #[\Override]
    protected function setUp(): void
    {
        $this->serializer = new JsonDtoSerializer();
        $this->serializer->setRootClasses(['TestInvoice' => TestInvoiceType::class]);
    }

    public function testSerializeProducesJsonRootElementFromRegisteredRootClass(): void
    {
        $data = json_decode($this->serializer->serialize(self::invoice()), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(
            [
                'TestInvoice' => [
                    'ID' => 'RE-42',
                    'IssueDate' => '2025-08-20',
                    'CopyIndicator' => true,
                    'PayableAmount' => ['currencyID' => 'EUR', 'value' => '199.90'],
                    'InvoiceTypeCode' => ['listID' => '100', 'value' => '380'],
                    'BuyerParty' => [
                        'PartyName' => 'Acme GmbH',
                        'PartyRole' => ['listID' => '305', 'value' => 'Buyer'],
                    ],
                    'Note' => ['first note', 'second note'],
                    'AdditionalParty' => [
                        ['PartyName' => 'Agent GmbH', 'PartyRole' => ['listID' => '407', 'value' => 'Agent']],
                        ['PartyName' => 'Sub GmbH', 'PartyRole' => ['listID' => '408', 'value' => 'Sub']],
                    ],
                ],
            ],
            $data
        );
    }

    public function testUnserializeRoundTripsBackToDto(): void
    {
        $json = $this->serializer->serialize(self::invoice());
        $dto = $this->serializer->unserialize($json);

        self::assertInstanceOf(TestInvoiceType::class, $dto);
        self::assertSame('RE-42', $dto->id->getValue());
        self::assertInstanceOf(DateTimeImmutable::class, $dto->issueDate->getValue());
        self::assertSame('2025-08-20', $dto->issueDate->getValue()->format('Y-m-d'));
        self::assertTrue($dto->copyIndicator->getValue());
        self::assertSame('EUR', $dto->payableAmount->currencyID);
        self::assertSame('199.90', $dto->payableAmount->getValue());
        self::assertSame('100', $dto->invoiceTypeCode->listID);
        self::assertSame('380', $dto->invoiceTypeCode->getValue());
        self::assertSame('Acme GmbH', $dto->buyerParty->partyName->getValue());
        self::assertSame('Buyer', $dto->buyerParty->partyRole->getValue());
        self::assertCount(2, $dto->note);
        self::assertSame('first note', iterator_to_array($dto->note)[0]->getValue());
        self::assertCount(2, $dto->additionalParty);
    }

    public function testSerializeUsesElementAttributeAsRootFallback(): void
    {
        $serializer = new JsonDtoSerializer();
        $data = json_decode($serializer->serialize(self::invoice()), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(['TestInvoice'], array_keys($data));
    }

    public function testUnserializeRejectsUnregisteredRootElement(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unknown root element "Bogus".');

        $this->serializer->unserialize('{"Bogus":{}}');
    }

    public function testUnserializeRejectsUnknownInnerElement(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unknown element(s) "Bogus"');

        $this->serializer->unserialize('{"TestInvoice":{"ID":"RE-42","Bogus":"x"}}');
    }

    public static function invoice(): TestInvoiceType
    {
        $id = new IdType();
        $id->setValue('RE-42');

        $date = new DateType();
        $date->setValue(new DateTimeImmutable('2025-08-20'));

        $indicator = new IndicatorType();
        $indicator->setValue(true);

        $amount = new AmountType('EUR');
        $amount->setValue('199.90');

        $code = new CodeType('100');
        $code->setValue('380');

        $buyerRole = new CodeType('305');
        $buyerRole->setValue('Buyer');
        $buyer = new PartyType(self::text(new NameType(), 'Acme GmbH'), $buyerRole);

        /** @var SplObjectStorage<NoteType, mixed> $notes */
        $notes = new SplObjectStorage();
        foreach (['first note', 'second note'] as $text) {
            $notes->offsetSet(self::text(new NoteType(), $text));
        }

        /** @var SplObjectStorage<PartyType, mixed> $additionalParties */
        $additionalParties = new SplObjectStorage();
        foreach ([['Agent GmbH', '407', 'Agent'], ['Sub GmbH', '408', 'Sub']] as [$name, $listId, $role]) {
            $roleType = new CodeType($listId);
            $roleType->setValue($role);
            $additionalParties->offsetSet(new PartyType(self::text(new NameType(), $name), $roleType));
        }

        return new TestInvoiceType($id, $date, $indicator, $amount, $code, $buyer, $notes, $additionalParties);
    }

    private static function text(NoteType|NameType $type, string $value): NoteType|NameType
    {
        $type->setValue($value);

        return $type;
    }
}