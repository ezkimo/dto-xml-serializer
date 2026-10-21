# JSON Serialization

Use `JsonDtoSerializer` to serialize DTOs to JSON and deserialize JSON to DTOs.

```php
use MMNewmedia\Serializer\JsonDtoSerializer;

$serializer = new JsonDtoSerializer();
```

## Root Classes

For deserialization, register root element names to classes:

```php
$serializer->setRootClasses([
    'Invoice' => InvoiceType::class,
    'CrossIndustryInvoice' => CrossIndustryInvoiceType::class,
]);
```

For serialization, the root element name is taken from the registered root class or from `#[Element]`/`#[RootElement]` attributes.

## Serializing

```php
$json = $serializer->serialize($invoice);
```

## Deserializing

```php
$invoice = $serializer->unserialize($json);
```

## Simple Content Representation

- Simple content types are serialized as scalar values by default.
- If a simple content type declares attributes, its JSON representation becomes an object with attributes as keys and the value under the key `value`.

Example:
```json
{
  "PayableAmount": {
    "currencyID": "EUR",
    "value": "199.90"
  }
}
```

## Validation

Deserialization and serialization validate enumerations and patterns. See [Validation](../validation.md).
