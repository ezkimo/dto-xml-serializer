# Documentation Overview

This library maps PHP objects to XML and JSON using XSD-like PHP attributes. The documentation is organized to show side-by-side mappings between XSD schema constructs and PHP classes with the corresponding attributes.

## Contents

### Getting Started
- [Getting Started](getting-started.md) - Installation, setup, and a quick example

### Mapping (XSD <-> PHP)
- [Simple Types](mapping/simple-types.md) - `SimpleType`, `Restriction`, `Enumeration`, `Pattern`, `MinLength`, `MaxLength`, `FractionDigits`, `MaxInclusive`, `MinExclusive`
- [Complex Types](mapping/complex-types.md) - `ComplexType`, `Element`, `Sequence`
- [Simple Content](mapping/simple-content.md) - `SimpleContent`, attributes on simple content, and deprecated patterns
- [Attributes](mapping/attributes.md) - XML attributes via `#[Attribute]`
- [Root Elements](mapping/root-elements.md) - `RootElement`, `Schema`, and namespaces

### Serialization
- [XML Serialization](serialization/xml.md) - `XmlDtoSerializer` usage and configuration
- [JSON Serialization](serialization/json.md) - `JsonDtoSerializer` usage and configuration

### Reference
- [Validation](validation.md) - Enumeration, pattern, length, and decimal validation
- [Namespaces](namespaces.md) - Namespace handling in detail
- [Migration Guide](migration.md) - Migrating from deprecated patterns
