# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [0.5.0] – 2025-07-05
[0.5.0]: https://github.com/nandan108/prop-access/compare/v0.4.0...v0.5.0

### Changed
- Introduced `AccessorException` for clearer error signaling on missing getters/setters
- Replaced previous `LogicException` usages in `AccessProxy`, `ObjectGetterResolver`, `ObjectSetterResolver`, and `StdClassGetterResolver`
- Improved internal consistency and diagnostic clarity

## [0.4.0] – 2025-06-29
[0.4.0]: https://github.com/nandan108/prop-access/compare/v0.3.0...v0.4.0

### Changed
- Renamed `AccessorRegistry` to `PropAccess` for coherence with package name.
- Renamed `AccessorProxy` to `AccessProxy`.
- Resolved static analysis issues and bumped Psalm errorLevel to 1
- Updated README to reflect these changes.

## [0.3.0] – 2025-06-24
[0.3.0]: https://github.com/nandan108/prop-access/compare/v0.2.0...v0.3.0

### Added
- `AccessorProxy` class: array-style, iterable, and countable wrapper for accessing properties via getters/setters
- `AccessorRegistry::getValueMap()` – directly returns values from getter map
- `AccessorRegistry::resolveValues()` – apply getter map to value source
- `AccessorRegistry::canGetGetterMap()` and `canGetSetterMap()` – test accessor support without throwing

### Changed
- Updated README to document new utilities and `AccessorProxy`

---

## [0.2.0] – 2025-06-22
### Added
- `AccessorRegistry::getValueMap()` to directly extract resolved values from a value source
- `AccessorRegistry::resolveValues()` to apply a getter map and resolve values
- `AccessorRegistry::canGetGetterMap()` and `canGetSetterMap()` to detect resolver support
- `$throwOnNotFound` option to `getGetterMap()` and `getSetterMap()` for graceful fallback

### Changed
- Default object resolvers now explicitly exclude `SplObjectStorage` and `WeakMap` to prevent ambiguous key/value behavior in querying contexts

[0.2.0]: https://github.com/nandan108/prop-access/compare/v0.1.1...v0.2.0
