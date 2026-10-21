# Getting Started

## Requirements

- PHP ^8.4|^8.5
- `ext-dom`, `ext-json`
- `laminas/laminas-serializer` ^4.0

## Installation

```bash
composer require mmnewmedia/dto-xml-serializer
```

## Basic Concepts

This library uses PHP 8 attributes to mirror XSD constructs:

- **Simple types** are represented by classes implementing `ValueableInterface` (via `ValueableTrait`), often annotated with `#[SimpleType]`, `#[Restriction]`, and facets like `#[Enumeration]` or `#[Pattern]`
- **Complex types** are annotated with `#[ComplexType]` and their properties with `#[Element]` plus `#[Sequence]` for ordering
- **XML attributes** on elements are represented by properties annotated with `#[Attribute]`
- **Root elements** are annotated with `#[RootElement]`

## Quick Example

### XSD

```xml
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" targetNamespace="urn:example:invoice">
  <xs:simpleType name="CurrencyCodeType">
    <xs:restriction base="xs:string">
      <xs:enumeration value="EUR"/>
      <xs:enumeration value="USD"/>
    </xs:restriction>
  </xs:simpleType>
</xs:schema>
```

### PHP

```php
<?php

use MMNewmedia\Model\ValueableInterface;
use MMNewmedia\Model\ValueableTrait;
use MMNewmedia\Xsd;

enum CurrencyCode: string
{
    case EUR = 'EUR';
    case USD = 'USD';
}

#[Xsd\SimpleType(name: 'CurrencyCodeType')]
#[Xsd\Restriction(base: 'xsd:string')]
#[Xsd\Enumeration(enum: CurrencyCode::class)]
final class CurrencyCodeType implements ValueableInterface
{
    use ValueableTrait;
}
```

## Serialization

### XML

```php
use MMNewmedia\Serializer\XmlDtoSerializer;

$serializer = new XmlDtoSerializer();
$serializer->setNamespaces(HeaderNamespaces::class);
// For UBL-style models, prefer property namespaces
$serializer->setUsePropertyNamespaces(true);

$xml = $serializer->serialize($invoice);
```

### JSON

```php
use MMNewmedia\Serializer\JsonDtoSerializer;

$serializer = new JsonDtoSerializer();
// Root name -> class mapping
$serializer->setRootClasses(['Invoice' => InvoiceType::class]);

$json = $serializer->serialize($invoice);
$invoice = $serializer->unserialize($json);
```

## Next Steps

- Learn about [Simple Types](mapping/simple-types.md) and facets like Enumeration and Pattern
- Explore [Complex Types](mapping/complex-types.md) and sequences
- Understand [Validation](validation.md) behavior
- Check the [Migration Guide](migration.md) if you're upgrading
