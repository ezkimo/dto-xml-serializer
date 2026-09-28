# mmnewmedia/dto-xml-serializer

DTO <-> XML and DTO <-> JSON serialization for PHP, driven by XSD-like PHP attribute classes.

The library maps your PHP objects to XML and JSON based on structural attributes that mirror the
XSD constructs the model is derived from. It is the serialization core originally extracted from the
[ezkimo/x-rechnung](https://github.com) project (xRechnung / EN 16931), where the generated models for
both CII (Cross-Industry Invoice) and UBL 2.1 use these attributes.

## Requirements

- PHP ^8.4
- `ext-dom`, `ext-json`
- laminas/laminas-serializer ^4.0

## Installation

```bash
composer require mmnewmedia/dto-xml-serializer
```

## Overview

```
MMNewmedia\Xsd\            PHP attribute classes that mirror XSD constructs
MMNewmedia\Serializer\     JsonDtoSerializer, XmlDtoSerializer (both Laminas Serializer adapters)
MMNewmedia\Data\           SequenceHeap for XSD sequence ordering
MMNewmedia\Model\          ValueableInterface / ValueableTrait (simple content contract)
```

### Annotating your DTOs

Leaf / simple content types implement `ValueableInterface` (via `ValueableTrait`) and describe their
XSD base type with `#[SimpleContent]` + `#[Extension]`:

```php
use MMNewmedia\Model\ValueableInterface;
use MMNewmedia\Model\ValueableTrait;
use MMNewmedia\Xsd;

#[Xsd\SimpleContent]
#[Xsd\Extension(base: 'xsd:date')]
final class IssueDateType implements ValueableInterface
{
    use ValueableTrait;
}

#[Xsd\SimpleContent]
#[Xsd\Extension(base: 'xsd:decimal')]
final class AmountType implements ValueableInterface
{
    use ValueableTrait;

    public function __construct(
        #[Xsd\Attribute(name: 'currencyID', type: 'xsd:string', use: Xsd\AttributeUseType::OPTIONAL)]
        public ?string $currencyID = null,
    ) {}
}
```

Complex types are annotated with `#[ComplexType]` (optionally `#[Schema]` for a target namespace) and
their properties with `#[Element]` plus `#[Sequence]` for XSD ordering; collections use
`SplObjectStorage`:

```php
use MMNewmedia\Xsd;
use SplObjectStorage;

#[Xsd\ComplexType]
#[Xsd\Schema(targetNamespace: 'urn:example:invoice')]
#[Xsd\Element(namespace: HeaderNamespaces::INV, name: 'Invoice', type: InvoiceType::class)]
final readonly class InvoiceType
{
    public function __construct(
        #[Xsd\Element(namespace: HeaderNamespaces::CBC, name: 'ID', type: IdType::class)]
        #[Xsd\Sequence(position: 1)]
        public IdType $id,
        #[Xsd\Element(name: 'Note', maxOccurs: 99, type: NoteType::class)]
        #[Xsd\Sequence(position: 2)]
        public SplObjectStorage $note,
    ) {}
}
```

### JSON

```php
use MMNewmedia\Serializer\JsonDtoSerializer;

$serializer = new JsonDtoSerializer();

// DTO -> JSON (root name is taken from the registered root class or the #[Element] attribute)
$json = $serializer->serialize($invoice);

// JSON -> DTO (requires the root element name to be registered)
$serializer->setRootClasses(['Invoice' => InvoiceType::class, 'CrossIndustryInvoice' => CrossIndustryInvoiceType::class]);
$invoice = $serializer->unserialize($json);
```

Simple content types are serialized as scalar values. If a simple content type declares attributes,
its JSON representation becomes an object with the attributes as keys and the value under the key
`value`.

### XML

```php
use MMNewmedia\Serializer\XmlDtoSerializer;

$serializer = new XmlDtoSerializer();

// Map namespace URIs to prefixes via a BackedEnum (case name => prefix after the last underscore)
$serializer->setNamespaces(HeaderNamespaces::class);

// For model families that declare the namespace per element (UBL), prefer element namespaces:
$serializer->setUsePropertyNamespaces(true);

$xml = $serializer->serialize($invoice);
```

With `usePropertyNamespaces` disabled (the default, matching the CII model), child elements inherit
the `targetNamespace` of the enclosing complex type's `#[Schema]` attribute; the property-level
`#[Element]` namespaces act as a fallback only.

### Namespaces enum

```php
enum HeaderNamespaces: string
{
    case INV = 'urn:example:invoice';
    case CBC = 'urn:example:commonbasic';
    case CAC = 'urn:example:commonaggregate';
}
```

## Tests

```bash
composer install
composer test
```

## Static analysis

```bash
composer psalm
```

Psalm runs with `errorLevel="5"` (mirroring the original project configuration). Note that Psalm treats
`ReflectionAttribute<T>` as invariant, so helpers that accept attributes read from specific
`#[Element]` / `#[Schema]` / `#[Sequence]` attributes are annotated with the exact generic, e.g.
`@param ReflectionAttribute<Element>`.

## License

This project is licensed under the BSD 3‑Clause License.  
See the [LICENSE](./LICENSE) file for the full text.