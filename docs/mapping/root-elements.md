# Root Elements & Schema (XSD <-> PHP)

## RootElement

Mark a class as the root element for serialization/deserialization.

**PHP:**
```php
use MMNewmedia\Xsd;

#[Xsd\RootElement(namespace: HeaderNamespaces::INV, name: 'Invoice', type: InvoiceType::class)]
final readonly class InvoiceType
{
    // ...
}
```

### `#[RootElement]` Properties

| Property | XSD | Description |
| --- | --- | --- |
| `abstract` | `@abstract` | Abstract element |
| `block` | `@block` | Blocked substitution/derivation |
| `default` | `@default` | Default value |
| `final` | `@final` | Final derivation |
| `id` | `@id` | Unique ID |
| `name` | `@name` | Element name |
| `namespace` | - | Namespace (BackedEnum or string) |
| `nillable` | `@nillable` | Nillable |
| `ref` | `@ref` | Element reference |
| `substitutionGroup` | `@substitutionGroup` | Substitution group |
| `type` | `@type` | Element type |

Targets: `TARGET_CLASS`

## Schema

Provide schema metadata (commonly target namespace).

**XSD:**
```xml
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" targetNamespace="urn:example:invoice" elementFormDefault="qualified"/>
```

**PHP:**
```php
use MMNewmedia\Xsd;

#[Xsd\ComplexType]
#[Xsd\Schema(targetNamespace: 'urn:example:invoice', elementFormDefault: 'qualified')]
final readonly class InvoiceType
{
    // ...
}
```

### `#[Schema]` Properties

| Property | XSD | Description |
| --- | --- | --- |
| `xmlns` | - | Schema namespace (BackedEnum or string) |
| `targetNamespace` | `@targetNamespace` | Target namespace (BackedEnum or string) |
| `elementFormDefault` | `@elementFormDefault` | Default form for elements |
| `attributeFormDefault` | `@attributeFormDefault` | Default form for attributes |
| `version` | `@version` | Schema version |

Targets: `TARGET_CLASS`
