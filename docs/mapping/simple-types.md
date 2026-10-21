# Simple Types (XSD <-> PHP)

Simple types in XSD are defined with `xs:simpleType`. In this library, they are typically modeled as classes implementing `ValueableInterface` (via `ValueableTrait`).

## SimpleType

**XSD:**
```xml
<xs:simpleType name="PostalCodeType">
  <xs:restriction base="xs:string">
    <xs:pattern value="[A-Z]{2}[0-9]{4}"/>
  </xs:restriction>
</xs:simpleType>
```

**PHP:**
```php
use MMNewmedia\Model\ValueableInterface;
use MMNewmedia\Model\ValueableTrait;
use MMNewmedia\Xsd;

#[Xsd\SimpleType(name: 'PostalCodeType')]
#[Xsd\Restriction(base: 'xsd:string')]
#[Xsd\Pattern(value: '[A-Z]{2}[0-9]{4}')]
final class PostalCodeType implements ValueableInterface
{
    use ValueableTrait;
}
```

### `#[SimpleType]` Properties

| Property | XSD | Description |
| --- | --- | --- |
| `name` | `@name` | Name of the simple type (for named types) |
| `id` | `@id` | Unique ID |
| `final` | `@final` | Derivation control (`#all`, `#restriction`, etc.) |

Targets: `TARGET_CLASS \| TARGET_PROPERTY`

## Restriction

**XSD:**
```xml
<xs:restriction base="xs:string">
  ...
</xs:restriction>
```

**PHP:**
```php
#[Xsd\Restriction(base: 'xsd:string')]
final class StringRestrictedType implements ValueableInterface
{
    use ValueableTrait;
}
```

### `#[Restriction]` Properties

| Property | XSD | Description |
| --- | --- | --- |
| `base` | `@base` | QName of the base type (e.g. `xsd:string`, `xs:decimal`) |
| `id` | `@id` | Unique ID |

Targets: `TARGET_CLASS \| TARGET_PROPERTY`

## Enumeration

Enumerations are facets of `xsd:restriction`. Use `#[Enumeration]` with a PHP `BackedEnum` as the single source of truth.

**XSD:**
```xml
<xs:simpleType name="CurrencyCodeType">
  <xs:restriction base="xs:string">
    <xs:enumeration value="EUR"/>
    <xs:enumeration value="USD"/>
  </xs:restriction>
</xs:simpleType>
```

**PHP:**
```php
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

### `#[Enumeration]` Properties

| Property | XSD | Description |
| --- | --- | --- |
| `enum` | - | Class-string of a `BackedEnum` (source of truth) |
| `name` | (on simpleType) | Optional name of the simple type |
| `base` | (on restriction) | Base type, defaults to `xsd:string` |
| `validate` | - | Enable/disable runtime validation (default `true`) |

## Pattern

Patterns constrain values with regular expressions. Multiple patterns are allowed via repeated attributes.

**XSD:**
```xml
<xs:simpleType name="Code3Type">
  <xs:restriction base="xs:string">
    <xs:pattern value="^[A-Z]{3}$"/>
  </xs:restriction>
</xs:simpleType>
```

**PHP:**
```php
#[Xsd\SimpleType(name: 'Code3Type')]
#[Xsd\Restriction(base: 'xsd:string')]
#[Xsd\Pattern(value: '^[A-Z]{3}$')]
final class Code3Type implements ValueableInterface
{
    use ValueableTrait;
}
```

### Multiple Patterns

**XSD:**
```xml
<xs:restriction base="xs:string">
  <xs:pattern value="^[A-Z]{3}$"/>
  <xs:pattern value="^[0-9]{3}$"/>
</xs:restriction>
```

**PHP:**
```php
#[Xsd\SimpleType(name: 'CodeType')]
#[Xsd\Restriction(base: 'xsd:string')]
#[Xsd\Pattern(value: '^[A-Z]{3}$')]
#[Xsd\Pattern(value: '^[0-9]{3}$')]
final class CodeType implements ValueableInterface
{
    use ValueableTrait;
}
```

### `#[Pattern]` Properties

| Property | XSD | Description |
| --- | --- | --- |
| `value` | `@value` | Regular expression pattern (required) |
| `id` | `@id` | Unique ID |

Targets: `TARGET_CLASS \| TARGET_PROPERTY \| Attribute::IS_REPEATABLE`

## Notes

- For enumerated simple types, the recommended pattern is `#[SimpleType] + #[Restriction] + #[Enumeration]` (see [Migration Guide](../migration.md)).
- Both serializers enforce validation for Enumerations and Patterns at runtime.
- `ValueableInterface` + `ValueableTrait` provide `getValue()`/`setValue()` for simple content.
