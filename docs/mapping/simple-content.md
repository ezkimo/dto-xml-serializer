# Simple Content (XSD <-> PHP)

Simple content is used when a complex type has simple content plus attributes (via `xs:simpleContent`). For pure simple types with restrictions/facets, prefer `#[SimpleType]` + `#[Restriction]` (see [Simple Types](simple-types.md)).

## SimpleContent with Extension

**XSD:**
```xml
<xs:complexType name="AmountType">
  <xs:simpleContent>
    <xs:extension base="xs:decimal">
      <xs:attribute name="currencyID" type="xs:string" use="optional"/>
    </xs:extension>
  </xs:simpleContent>
</xs:complexType>
```

**PHP:**
```php
use MMNewmedia\Model\ValueableInterface;
use MMNewmedia\Model\ValueableTrait;
use MMNewmedia\Xsd;

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

### `#[SimpleContent]` Properties

| Property | XSD | Description |
| --- | --- | --- |
| `id` | `@id` | Unique ID |

Targets: `TARGET_CLASS`

### `#[Extension]` Properties

| Property | XSD | Description |
| --- | --- | --- |
| `base` | `@base` | Base type QName |
| `id` | `@id` | Unique ID |

Targets: `TARGET_CLASS`

> **Deprecated:** `#[Extension]` is deprecated in favor of `#[Restriction]` for enumerated/pattern-constrained simple types. See [Migration Guide](../migration.md).

## Attributes on Simple Content

Attributes on simple content types are represented with `#[Attribute]` on constructor properties (see [Attributes](attributes.md)).
