# Namespaces

The library handles XML namespaces via namespace enums and serializer configuration.

## Defining Namespace Mappings

Define a BackedEnum mapping namespace URIs to prefixes. The case name determines the prefix (after the last underscore if present).

```php
enum HeaderNamespaces: string
{
    case INV = 'urn:example:invoice';
    case CBC = 'urn:example:commonbasic';
    case CAC = 'urn:example:commonaggregate';
    case UDT = 'urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100';
}
```

Configure the serializer:

```php
use MMNewmedia\Serializer\XmlDtoSerializer;

$serializer = new XmlDtoSerializer();
$serializer->setNamespaces(HeaderNamespaces::class);
```

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
