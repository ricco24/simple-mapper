# Change log

## [Unreleased][unreleased]
#### Added
- Repository class
- Behaviours
- Scopes
- ActiveRow has referencingRecord

#### Changed
- Add PHP 7.1 support only (BC break)

#### Fixed
- ActiveRow ArrayAccess for related table with registered mapping

## 0.5.1 - 2016-12-03
### Fixed
- Fixed selection clone behaviour

## 0.5.0 - 2016-11-27
### Added
- Support for activeRow __get() transformation by defined structure

## 0.4.1 - 2016-11-24
### Fixed
- Selection order method with expanded params

## 0.4.0 - 2016-11-23
### Added
- ActiveRow implements Nette\Database\Table\IRow

## 0.3.0 - 2016-11-23
### Added
- Structure class
- ref()/related() wrapper methods to ActiveRow
- mmRelated() method to ActiveRow

### Changed
- Remove custom related/referenced methods

## 0.2.1 - 2016-11-18
### Fixed
- getReferenced method in ActiveRow

## 0.2.0 - 2016-11-18
### Changed
- removed internal cache

## 0.1.0
- Initial version

[unreleased]: https://github.com/ricco24/simple-mapper/compare/0.5.1...HEAD
[0.5.1]: https://github.com/ricco24/simple-mapper/compare/0.5.0...0.5.1
[0.5.0]: https://github.com/ricco24/simple-mapper/compare/0.4.1...0.5.0
[0.4.1]: https://github.com/ricco24/simple-mapper/compare/0.4.0...0.4.1
[0.4.0]: https://github.com/ricco24/simple-mapper/compare/0.3.0...0.4.0
[0.3.0]: https://github.com/ricco24/simple-mapper/compare/0.2.1...0.3.0
[0.2.1]: https://github.com/ricco24/simple-mapper/compare/0.2.0...0.2.1
[0.2.0]: https://github.com/ricco24/simple-mapper/compare/0.1.0...0.2.0