# Migration Guide

## Deprecated: `#[SimpleContent] + #[Extension] + #[Enumeration]`

In XSD, enumerations are facets of `xs:restriction`, not `xs:extension`. The previous pattern `#[SimpleContent] + #[Extension] + #[Enumeration]` is incorrect in terms of XSD semantics and has been deprecated in favor of `#[SimpleType] + #[Restriction] + #[Enumeration]`.

The deprecated pattern remains functional for backward compatibility but will be removed in a future major release.

### Before (Deprecated)

```php
use MMNewmedia\Model\ValueableInterface;
use MMNewmedia\Model\ValueableTrait;
use MMNewmedia\Xsd;

enum CurrencyCode: string
{
    case EUR = 'EUR';
    case USD = 'USD';
}

#[Xsd\SimpleContent]
#[Xsd\Extension(base: 'xsd:string')]
#[Xsd\Enumeration(enum: CurrencyCode::class, name: 'CurrencyCodeType')]
final class CurrencyCodeType implements ValueableInterface
{
    use ValueableTrait;
}
```

### After (Recommended)

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

### XSD Comparison

**Deprecated mapping (conceptually):**
```xml
<xs:complexType>
  <xs:simpleContent>
    <xs:extension base="xs:string">
      <xs:enumeration value="EUR"/>
      ...
    </xs:extension>
  </xs:simpleContent>
</xs:complexType>
```

**Correct mapping:**
```xml
<xs:simpleType name="CurrencyCodeType">
  <xs:restriction base="xs:string">
    <xs:enumeration value="EUR"/>
    <xs:enumeration value="USD"/>
  </xs:restriction>
</xs:simpleType>
```

## Notes

- `#[Extension]` is marked with `@deprecated` in the codebase.
- Both patterns currently work, but new code should use the `SimpleType` + `Restriction` approach.
- See [Simple Types](mapping/simple-types.md) for full details on the new attributes.
