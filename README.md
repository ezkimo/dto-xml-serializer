# mmnewmedia/dto-xml-serializer

DTO -> XML and DTO <-> JSON serialization for PHP, driven by XSD-like PHP attribute classes.

The library maps your PHP objects to XML and JSON based on structural attributes that mirror the XSD constructs the model is derived from.

## Requirements

- PHP ^8.4|^8.5
- `ext-dom`, `ext-json`, `ext-mbstring`
- `laminas/laminas-serializer` ^4.0

## Installation

```bash
composer require mmnewmedia/dto-xml-serializer
```

## Quick Start

```php
use MMNewmedia\Model\ValueableInterface;
use MMNewmedia\Model\ValueableTrait;
use MMNewmedia\Xsd;

#[Xsd\SimpleType]
#[Xsd\Restriction(base: 'xsd:string')]
#[Xsd\Enumeration(enum: CurrencyCode::class)]
final class CurrencyCodeType implements ValueableInterface
{
    use ValueableTrait;
}
```

```php
use MMNewmedia\Serializer\XmlDtoSerializer;

$serializer = new XmlDtoSerializer();
$serializer->setNamespaces(HeaderNamespaces::class);
$xml = $serializer->serialize($invoice);
```

```php
use MMNewmedia\Serializer\JsonDtoSerializer;

$serializer = new JsonDtoSerializer();
$serializer->setRootClasses(['Invoice' => InvoiceType::class]);
$invoice = $serializer->unserialize($json);
```

## Documentation

See the full documentation in the [`/docs`](docs/) directory:

- [Overview](docs/index.md)
- [Getting Started](docs/getting-started.md)
- [Mapping](docs/mapping/) - XSD <-> PHP mappings for simple types, complex types, attributes, root elements
- [Serialization](docs/serialization/) - XML and JSON serialization
- [Validation](docs/validation.md)
- [Namespaces](docs/namespaces.md)
- [Migration Guide](docs/migration.md)

## Tests

```bash
composer install
composer test
```

## Static Analysis

```bash
composer psalm
```

Psalm runs with `errorLevel="5"`. See `psalm.xml` for details.

## License

This project is licensed under the BSD 3‑Clause License.  
See the [LICENSE](./LICENSE) file for the full text.
