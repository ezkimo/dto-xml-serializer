# XML Serialization

Use `XmlDtoSerializer` to serialize DTOs to XML and (where implemented) deserialize XML to DTOs.

```php
use MMNewmedia\Serializer\XmlDtoSerializer;

$serializer = new XmlDtoSerializer();
```

## Namespaces

Map namespace URIs to prefixes using a BackedEnum. The case name maps to the prefix after the last underscore.

```php
enum HeaderNamespaces: string
{
    case INV = 'urn:example:invoice';
    case CBC = 'urn:example:commonbasic';
    case CAC = 'urn:example:commonaggregate';
}

$serializer->setNamespaces(HeaderNamespaces::class);
```

## Property vs Schema Namespaces

- By default (`usePropertyNamespaces = false`), matching the CII model, child elements inherit the `targetNamespace` of the enclosing complex type's `#[Schema]`. Property-level `#[Element]` namespaces act as a fallback.
- For UBL-style models where namespaces are declared per element, prefer element namespaces:

```php
$serializer->setUsePropertyNamespaces(true);
```

## Serializing

```php
$xml = $serializer->serialize($invoice);
```

The root element is determined from `#[RootElement]` on the class. Namespaces are declared on the root element based on the namespace mapping.

## Simple Content with Attributes

Simple content types with attributes (e.g. `AmountType` with `currencyID`) are serialized as elements with attributes:

```php
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

## Collections

Use `SplObjectStorage` for collections of elements. The serializer repeats elements when `maxOccurs > 1`.

## Validation

Serialization validates enumerations and patterns on simple content values. See [Validation](../validation.md).
