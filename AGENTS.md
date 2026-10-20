# AGENTS.md

## Project Overview

MMNewmedia/dto-xml-serializer is a PHP 8.4+ library for DTO <-> XML and DTO <-> JSON serialization driven by XSD-like PHP attribute classes. It is the serialization core originally extracted from the ezkimo/x-rechnung project (xRechnung / EN 16931), where the generated models for both CII (Cross-Industry Invoice) and UBL 2.1 use these attributes. The library uses Laminas Serializer adapters.

## Tech Stack

- PHP ^8.4|^8.5
- ext-dom, ext-json
- laminas/laminas-serializer ^4.0
- PHPUnit ^13.0, Psalm ^6.5

## Project Structure

- `src/Xsd/` - PHP attribute classes that mirror XSD constructs (Element, ComplexType, SimpleContent, Extension, SimpleType, Restriction, Pattern, Enumeration, Attribute, Sequence, Schema, RootElement, etc.)
- `src/Serializer/` - JsonDtoSerializer, XmlDtoSerializer (Laminas Serializer adapters), and validation traits (EnumerationValidator, PatternValidator)
- `src/Data/` - SequenceHeap for XSD sequence ordering
- `src/Model/` - ValueableInterface / ValueableTrait for simple content types

## Conventions

- Attributes are `final readonly` classes with `declare(strict_types=1)`, namespace `MMNewmedia\Xsd`
- Constructor properties are public (mirroring XSD element attributes)
- Attributes use `#[Attribute(...)]` with appropriate targets (`TARGET_CLASS`, `TARGET_PROPERTY`, or combined)
- Repeatable attributes use `Attribute::IS_REPEATABLE` (e.g. `Pattern`)
- Serializers use reflection to read attributes and validate values
- Enumerations are validated via `EnumerationValidator` trait walking inheritance tree
- Patterns are validated via `PatternValidator` trait walking inheritance tree (separate from enumeration validation)
- Simple content types implement `ValueableInterface` via `ValueableTrait`
- Namespace handling: set namespace enum via `setNamespaces()`, use property-level namespaces with `setUsePropertyNamespaces(true)`
- Sequence ordering is managed via `#[Sequence(position: ...)]` and `SequenceHeap`

## Testing

- Tests: `composer test` or `vendor/bin/phpunit`
- Static analysis: `composer psalm` (Psalm level 5)
- Test fixtures in `tests/Fixtures/`
- Unit tests in `tests/Xsd/`, serializer tests in `tests/Serializer/`

## Development Guidelines

- Follow existing attribute patterns when adding new XSD constructs
- Keep validation logic separate by concern (enumeration vs pattern) - use separate validator traits
- Maintain backward compatibility; deprecate via `@deprecated` docblocks when replacing patterns
- All classes must be strictly typed with `declare(strict_types=1)`
- Use PSR-4 autoloading as configured in composer.json

## Recent Changes

- Added `#[SimpleType]`, `#[Restriction]`, `#[Pattern]` attributes (issue #5)
- Added `PatternValidator` trait for pattern validation (separated from `EnumerationValidator`)
- Deprecated `#[SimpleContent] + #[Extension] + #[Enumeration]` in favor of `#[SimpleType] + #[Restriction] + #[Enumeration]` for enumerated simple types
- Marked `Extension` as deprecated with `@deprecated` docblock
- Pattern validation enforced by both serializers, throwing `RuntimeException` on mismatch
