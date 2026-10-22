# Validation

Both serializers validate simple content values against declared facets (enumeration, pattern, and length).

## Enumeration Validation

Enumerations are validated via `EnumerationValidator`. Values are checked against the enum-derived value set.

```php
use MMNewmedia\Model\ValueableInterface;
use MMNewmedia\Model\ValueableTrait;
use MMNewmedia\Xsd;

enum CurrencyCode: string
{
    case EUR = 'EUR';
    case USD = 'USD';
}

#[Xsd\SimpleType]
#[Xsd\Restriction(base: 'xsd:string')]
#[Xsd\Enumeration(enum: CurrencyCode::class)]
final class CurrencyCodeType implements ValueableInterface
{
    use ValueableTrait;
}
```

If validation fails, a `RuntimeException` is thrown:

```
Value 'CHF' is not part of enumeration 'CurrencyCodeType' (EUR, USD).
```

- Validation walks the inheritance tree.
- For `BackedEnum` values, the backed value is validated.
- Set `validate: false` to disable runtime validation: `#[Xsd\Enumeration(enum: CurrencyCode::class, validate: false)]`
- Integer-backed enums are supported (values compared as strings).

## Pattern Validation

Patterns are validated via `PatternValidator` (separate from enumeration validation to follow separation of concerns).

```php
#[Xsd\SimpleType(name: 'PostalCodeType')]
#[Xsd\Restriction(base: 'xsd:string')]
#[Xsd\Pattern(value: '[A-Z]{2}[0-9]{4}')]
final class PostalCodeType implements ValueableInterface
{
    use ValueableTrait;
}
```

### Multiple Patterns

Multiple `#[Pattern]` attributes are supported (repeatable). Following the XSD specification, multiple pattern facets of a restriction are **combined with AND** - a value has to match *every* declared pattern:

```php
#[Xsd\SimpleType(name: 'CodeType')]
#[Xsd\Restriction(base: 'xsd:string')]
#[Xsd\Pattern(value: '^[A-Z]{3}$')]
#[Xsd\Pattern(value: '^[^0-9]+$')]
final class CodeType implements ValueableInterface
{
    use ValueableTrait;
}
```

A value like `ABC` is valid, while `AB1` violates the second pattern.

If a value doesn't match a pattern, a `RuntimeException` is thrown:

```
Value 'abc' does not match pattern '^[A-Z]{3}$'.
```

### Notes

- Pattern validation is applied during serialization and deserialization in both `XmlDtoSerializer` and `JsonDtoSerializer`.
- Non-scalar values (e.g. `DateTimeInterface`) are skipped for pattern/enumeration scalar validation as appropriate.
- Invalid regex patterns are gracefully skipped for robustness.
- Validation walks the inheritance tree like enumeration validation.

## Length Validation

`minLength` and `maxLength` are validated via `StringLengthValidator` (separate from enumeration and pattern validation).

```php
#[Xsd\SimpleType(name: 'NameType')]
#[Xsd\Restriction(base: 'xsd:string')]
#[Xsd\MinLength(1)]
#[Xsd\MaxLength(35)]
final class NameType implements ValueableInterface
{
    use ValueableTrait;
}
```

Both bounds are inclusive:

- a value with exactly `minLength` characters is valid
- a value with exactly `maxLength` characters is valid

If a value is outside the declared bounds, a `RuntimeException` is thrown:

```
Value 'AB' is shorter than minLength 3 (length 2).
Value 'ABCDEF' is longer than maxLength 5 (length 6).
```

### Inheritance

Inherited length declarations are treated as an intersection, matching XSD restriction derivation:

- effective `minLength` = highest declaration in the inheritance chain
- effective `maxLength` = lowest declaration in the inheritance chain

### Notes

- Length is measured in UTF-8 characters via `mb_strlen()`, not in bytes.
- Validation is applied during serialization and deserialization in both `XmlDtoSerializer` and `JsonDtoSerializer`.
- Non-scalar values (e.g. `DateTimeInterface`) are skipped, consistent with enumeration and pattern validation.
- Whitespace is not normalized; length is measured on the stored value as-is.
- Validation order is enumeration, then pattern, then length.
