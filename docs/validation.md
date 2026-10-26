# Validation

Both serializers validate simple content values against declared facets (enumeration, pattern, length, and decimal constraints).

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

## Decimal Validation

`fractionDigits`, `maxInclusive`, and `minExclusive` are validated via `DecimalValidator` (separate from enumeration, pattern, and length validation).

```php
#[Xsd\SimpleType(name: 'AmountType')]
#[Xsd\Restriction(base: 'xsd:decimal')]
#[Xsd\FractionDigits(2)]
#[Xsd\MaxInclusive('999999.99')]
#[Xsd\MinExclusive('0')]
final class AmountType implements ValueableInterface
{
    use ValueableTrait;
}
```

### FractionDigits

`fractionDigits` limits the number of digits in the fractional part. Trailing zeros do not count:

- `1.2300` is valid with `fractionDigits=2`
- `1.234` is invalid with `fractionDigits=2`
- `0.001` is invalid with `fractionDigits=2`

If a value has too many fractional digits, a `RuntimeException` is thrown:

```
Value '12.345' has more than 2 fraction digits (3).
```

### MaxInclusive

`maxInclusive` is inclusive: a value equal to the bound is valid, a value above it is not.

```
Value '100.01' is greater than maxInclusive 100.00.
```

### MinExclusive

`minExclusive` is exclusive: a value must be strictly greater than the bound.

```
Value '0' is not greater than minExclusive 0.
```

### Inheritance

Inherited decimal declarations are treated as an intersection, matching XSD restriction derivation:

- effective `fractionDigits` = lowest declaration in the inheritance chain
- effective `maxInclusive` = lowest declaration in the inheritance chain
- effective `minExclusive` = highest declaration in the inheritance chain

### Exact Comparison

Bounds are compared as normalized decimal strings rather than floats, so values near the boundary and values above `2^53` are compared exactly. No `ext-bcmath` dependency is required.

`maxInclusive` and `minExclusive` accept `int`, `float`, or `string`. Passing a string is recommended for high-precision bounds:

```php
#[Xsd\MaxInclusive('9007199254740993')]
```

### Non-Decimal Values

When a type declares decimal facets, a value that cannot be parsed as a decimal is rejected:

```
Value 'abc' is not a valid decimal.
```

Non-scalar values (e.g. `DateTimeInterface`) are skipped, consistent with the other validators.

### Notes

- Validation is applied during serialization and deserialization in both `XmlDtoSerializer` and `JsonDtoSerializer`.
- `BackedEnum` values are unwrapped before validation.
- Normalization accepts `int`, `float`, and numeric strings, including leading signs and exponent notation produced by PHP float-to-string conversion.
- Validation order is enumeration, pattern, length, then decimal facets.
