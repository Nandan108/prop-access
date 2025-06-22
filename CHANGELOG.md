# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
