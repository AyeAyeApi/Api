# Change Log

## [Unreleased]

## [1.0.0] - 2016-02-17
### Changed
- Now uses Formatters 1.0.0
- My email is now listed as @ayeayeapi.com. Hopefully this will keep important Aye Aye related stuff out of the noise
  of my older inbox.
- New license will be MIT
- Updated badges in README

### Removed
- References to old tests had been left in composer.json
- Build folder contained scripts for building in Jenkins. This has all been moved to composer.json scripts

## [1.0.0-rc.2] - 2016-02-17
### Added
- Added this file
- Tests now lint first
- Report script creates detailed artifacts for PHPCS, PHPLOC, PDEPEND, PHPCPD
- Injector traits to help with DI while keeping things simple.
- ReflectionController and ControllerReflector used to simplify Router and provide DI.
- Reader Factory now decodes Request bodies
 
### Changed
- Higher quality PHP DocBlocks for all classes and methods
- Tests no longer use TestData
- Response body is now returned as an array instead of object

### Removed
- `->getData()` in Response was unnecessary, use `->getBody()` instead.
- `->setStatusCode()` in Controller and Response breaks DI and was unnecessary, use `->setStatus()` instead.
- Router and LoggerInterface are no longer part of the API constructor. Use the setters provided by the injectors 
  instead.

## 1.0.0-rc.1 - 2015-09-02 
### Added
- 1.0.0 release candidate 

[Unreleased]: https://github.com/AyeAyeApi/Api/compare/1.0.0...HEAD
[1.0.0]: https://github.com/AyeAyeApi/Api/compare/1.0.0-rc.2...1.0.0
[1.0.0-rc.2]: https://github.com/AyeAyeApi/Api/compare/1.0.0-rc.1...1.0.0-rc.2

