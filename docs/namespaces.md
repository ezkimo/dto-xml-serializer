# Namespaces

The library maps namespace URIs to XML prefixes at serialization time. Prefixes are a
serialization concern: they are configured on `XmlDtoSerializer`, not in the DTO attributes.

## Recommended: Explicit Prefix Map

Pass a map of namespace URIs to prefixes to `setNamespacePrefixes()`. Keys are matched exactly
against the URIs used in `#[Xsd\Element]`, `#[Xsd\Schema]` and `#[Xsd\RootElement]`. Values are
used verbatim as XML prefixes, including their case.

```php
use MMNewmedia\Serializer\XmlDtoSerializer;

$serializer = new XmlDtoSerializer();
$serializer->setNamespacePrefixes([
    'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2' => 'ubl',
    'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2' => 'cbc',
    'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100' => 'ram',
]);
```

No namespace enum is required when an explicit map is used.

Only namespaces that are actually used by the serialized document are declared, exactly once,
on the root element. Unused entries in the map do not produce `xmlns` declarations and cannot
break serialization.

### Validation

Every entry is validated when `setNamespacePrefixes()` is called, even if it is unused. The
method throws `InvalidArgumentException` for:

- an empty prefix,
- a prefix that is not a valid XML NCName (for example a leading digit, `:`, or whitespace),
- the reserved prefixes `xml` and `xmlns` (case-insensitive),
- the same prefix configured for two different namespace URIs (case-sensitive, as XML prefixes
  are case-sensitive).

If two *used* namespaces resolve to the same prefix, or a used namespace has no configured
mapping at all, serialization throws a descriptive `LogicException` that names the URI and the
element instead of emitting broken XML.

## Fallback: Namespace Enum

`setNamespaces()` maps namespace URIs to prefixes via the case names of a `BackedEnum`. The
prefix is the substring after the last underscore, lowercased. This behavior is kept as a
backwards-compatible fallback; prefer the explicit map above.

```php
enum HeaderNamespaces: string
{
    case INV = 'urn:example:invoice';
    case CBC = 'urn:example:commonbasic';
    case CAC = 'urn:example:commonaggregate';
    case UDT = 'urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100';
}

$serializer->setNamespaces(HeaderNamespaces::class);
```

Enum case names cannot always be mapped to valid, unique XML prefixes. Enum-derived prefixes
are therefore validated lazily: an invalid or colliding prefix only fails when the corresponding
namespace is actually used. Unused enum cases are not declared and do not affect serialization.

When both are configured, the explicit map wins per URI. Namespace URIs without an explicit
entry still fall back to the enum-derived prefix.

## Schema Target Namespace

Use `#[Schema]` on complex types to define the target namespace.

```php
#[Xsd\ComplexType]
#[Xsd\Schema(targetNamespace: 'urn:example:invoice')]
final readonly class InvoiceType
{
    // ...
}
```

## Element Namespaces

Specify namespace per element:

```php
#[Xsd\Element(name: 'ID', type: IdType::class, namespace: Namespaces::CBC)]
#[Xsd\Sequence(position: 1)]
public IdType $id,
```

## Property vs Schema Namespaces

- **Default (`usePropertyNamespaces = false`)** - CII model style: child elements inherit the enclosing complex type's `targetNamespace` from `#[Schema]`. Property-level `#[Element]` namespaces act as a fallback.
- **`usePropertyNamespaces = true`** - UBL model style: element namespaces declared on the property take precedence.

```php
$serializer->setUsePropertyNamespaces(true);
```

## Root Element Namespaces

Specify namespace on `#[RootElement]`:

```php
#[Xsd\RootElement(namespace: HeaderNamespaces::INV, name: 'Invoice', type: InvoiceType::class)]
final readonly class InvoiceType
{
    // ...
}
```
