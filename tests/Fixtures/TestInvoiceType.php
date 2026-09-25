<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

use MMNewmedia\Xsd;
use SplObjectStorage;

#[Xsd\ComplexType]
#[Xsd\Schema(
    targetNamespace: 'urn:example:invoice',
    xmlns: 'urn:example:invoice',
)]
#[Xsd\Element(namespace: Namespaces::INV, name: 'TestInvoice', type: TestInvoiceType::class)]
final readonly class TestInvoiceType
{
    public function __construct(
        #[Xsd\Element(namespace: Namespaces::CBC, name: 'ID', type: IdType::class)]
        #[Xsd\Sequence(position: 1)]
        public IdType $id,
        #[Xsd\Element(namespace: Namespaces::CBC, name: 'IssueDate', type: DateType::class)]
        #[Xsd\Sequence(position: 2)]
        public DateType $issueDate,
        #[Xsd\Element(namespace: Namespaces::CBC, name: 'CopyIndicator', type: IndicatorType::class)]
        #[Xsd\Sequence(position: 3)]
        public IndicatorType $copyIndicator,
        #[Xsd\Element(namespace: Namespaces::CAC, name: 'PayableAmount', type: AmountType::class)]
        #[Xsd\Sequence(position: 4)]
        public AmountType $payableAmount,
        #[Xsd\Element(namespace: Namespaces::CBC, name: 'InvoiceTypeCode', type: CodeType::class)]
        #[Xsd\Sequence(position: 5)]
        public CodeType $invoiceTypeCode,
        #[Xsd\Element(namespace: Namespaces::CAC, name: 'BuyerParty', type: PartyType::class)]
        #[Xsd\Sequence(position: 6)]
        public PartyType $buyerParty,
        /**
         * @var SplObjectStorage<NoteType, mixed>
         */
        #[Xsd\Element(namespace: Namespaces::CBC, name: 'Note', maxOccurs: 99, type: NoteType::class)]
        #[Xsd\Sequence(position: 7)]
        public SplObjectStorage $note,
        /**
         * @var SplObjectStorage<PartyType, mixed>
         */
        #[Xsd\Element(namespace: Namespaces::CAC, name: 'AdditionalParty', maxOccurs: 99, type: PartyType::class)]
        #[Xsd\Sequence(position: 8)]
        public SplObjectStorage $additionalParty,
    ) {}
}