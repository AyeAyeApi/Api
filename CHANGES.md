# Change Log

## [Unreleased]

## [1.0.0-rc.2] - 2016-02-17
### Added
- Tests now lint first
- Report script creates detailed artifacts for PHPCS, PHPLOC, PDEPEND, PHPCPD
- Injector traits to help with DI while keeping things simple.
- ReflectionController and ControllerReflector used to simplify Router and provide DI.
- Reader Factory now decodes Request bodies
 
### Changed
- Higher quality PHP DocBlocks for all classes and methods
- Tests no longer use TestData
- Response body now an array instead of object

### Removed
- `->getData()` in Response was unnecessary, use `->getBody()` instead.
- `->setStatusCode()` in Controller and Response breaks DI and was unnecessary, use `->setStatus()` instead.
- Router and LoggerInterface are no longer part of the API constructor. Use the setters provided by the injectors 
  instead.

## 1.0.0-rc.1 - 2015-09-02 
### Added
- 1.0.0 release candidate 

[Unreleased]: https://github.com/AyeAyeApi/Api/compare/1.0.0-rc.2...HEAD
[1.0.0-rc.2]: https://github.com/AyeAyeApi/Api/compare/1.0.0-rc.1...1.0.0-rc.2

