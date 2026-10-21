# Attributes (XSD <-> PHP)

XML attributes are represented with `#[Attribute]` on properties.

## XSD

```xml
<xs:attribute name="currencyID" type="xs:string" use="optional"/>
```

## PHP

```php
use MMNewmedia\Xsd;

public function __construct(
    #[Xsd\Attribute(name: 'currencyID', type: 'xsd:string', use: Xsd\AttributeUseType::OPTIONAL)]
    public ?string $currencyID = null,
) {}
```

### `#[Attribute]` Properties

| Property | XSD | Description |
| --- | --- | --- |
| `default` | `@default` | Default value |
| `fixed` | `@fixed` | Fixed value |
| `form` | `@form` | `qualified` or `unqualified` (`AttributeFormType`) |
| `id` | `@id` | Unique ID |
| `name` | `@name` | Attribute name |
| `ref` | `@ref` | Attribute reference |
| `type` | `@type` | Attribute type |
| `use` | `@use` | `optional`, `prohibited`, or `required` (`AttributeUseType`) |

Targets: `TARGET_PROPERTY`

### Enums

- `MMNewmedia\Xsd\AttributeFormType`
- `MMNewmedia\Xsd\AttributeUseType`
