# Complex Types (XSD <-> PHP)

Complex types are defined with `xs:complexType`. In this library, they are typically annotated with `#[ComplexType]` and have properties annotated with `#[Element]` and ordered with `#[Sequence]`.

## Basic Complex Type

**XSD:**
```xml
<xs:complexType name="PartyType">
  <xs:sequence>
    <xs:element name="PartyName" type="xs:string" minOccurs="1"/>
    <xs:element name="PartyRole" type="xs:string" minOccurs="0"/>
  </xs:sequence>
</xs:complexType>
```

**PHP:**
```php
use MMNewmedia\Xsd;

#[Xsd\ComplexType(name: 'PartyType')]
#[Xsd\Schema(targetNamespace: 'urn:example:invoice')]
final readonly class PartyType
{
    public function __construct(
        #[Xsd\Element(name: 'PartyName', type: NameType::class)]
        #[Xsd\Sequence(position: 1)]
        public NameType $partyName,

        #[Xsd\Element(name: 'PartyRole', type: CodeType::class, minOccurs: 0)]
        #[Xsd\Sequence(position: 2)]
        public ?CodeType $partyRole = null,
    ) {}
}
```

### `#[ComplexType]` Properties

| Property | XSD | Description |
| --- | --- | --- |
| `abstract` | `@abstract` | Abstract type |
| `block` | `@block` | Blocked derivation types |
| `final` | `@final` | Final derivation types |
| `id` | `@id` | Unique ID |
| `mixed` | `@mixed` | Mixed content |
| `name` | `@name` | Type name |

Targets: `TARGET_CLASS`

## Element

**XSD:**
```xml
<xs:element name="ID" type="cbc:IDType" minOccurs="1" maxOccurs="1"/>
```

**PHP:**
```php
#[Xsd\Element(name: 'ID', type: IdType::class, namespace: Namespaces::CBC)]
#[Xsd\Sequence(position: 1)]
public IdType $id,
```

### `#[Element]` Properties

| Property | XSD | Description |
| --- | --- | --- |
| `abstract` | `@abstract` | Abstract element |
| `block` | `@block` | Blocked substitution/derivation |
| `default` | `@default` | Default value |
| `final` | `@final` | Final derivation |
| `id` | `@id` | Unique ID |
| `maxOccurs` | `@maxOccurs` | Maximum occurrences |
| `minOccurs` | `@minOccurs` | Minimum occurrences |
| `name` | `@name` | Element name (overrides property name) |
| `namespace` | - | Namespace (can be BackedEnum or string) |
| `nillable` | `@nillable` | Nillable |
| `ref` | `@ref` | Element reference |
| `type` | `@type` | Element type |

Targets: `TARGET_PROPERTY`

## Sequence

Use `#[Sequence]` to control element order as in XSD sequences.

**PHP:**
```php
#[Xsd\Element(name: 'Note', type: NoteType::class, maxOccurs: 99)]
#[Xsd\Sequence(position: 2)]
public SplObjectStorage $note,
```

### `#[Sequence]` Properties

| Property | XSD | Description |
| --- | --- | --- |
| `position` | - | Order position in sequence |

Targets: `TARGET_PROPERTY`

## Collections

Collections of elements are represented with `SplObjectStorage`:

```php
#[Xsd\Element(name: 'Note', type: NoteType::class, maxOccurs: 99)]
#[Xsd\Sequence(position: 1)]
public SplObjectStorage $notes,
```

## Notes

- With `XmlDtoSerializer`, child elements inherit the enclosing complex type's `targetNamespace` from `#[Schema]` by default. Use `setUsePropertyNamespaces(true)` for models where element namespaces are declared per-property (UBL style).
- Sequence ordering is handled by `SequenceHeap` in `src/Data/`.
