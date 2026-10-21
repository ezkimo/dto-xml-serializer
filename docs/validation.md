# Validation

Both serializers validate simple content values against declared facets.

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
