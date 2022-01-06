# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [unreleased]

### Added
- `Flowcontrol\React\Inotify\InotifyStream::addWatch` method to add a new watcher
- `Flowcontrol\React\Inotify\InotifyStream::rmWatch` remove a prior registered watcher
- Support for PHP 8.1

### Changed
- `Flowcontrol\Reacht\Inotify\InotifyStream::__construct` does not need inotify stream or event loop anymore

### Removed
- `Flowcontrol\React\Inotify\InotifyStream::isReadable`
- `Flowcontrol\React\Inotify\InotifyStream::pause`
- `Flowcontrol\React\Inotify\InotifyStream::resume`
- `Flowcontrol\React\Inotify\InotifyStream::close`

## [1.1.1] - 2021-01-04

### Changed
- switched from Travis-CI to GitHub Actions
- Bumped depedencies

## [1.1.0] - 2020-09-26

### Added
- PHP 8 support

### Changed 
- Updated development dependencies
- Will not close file pointer anymore in library

## [1.0.2] - 2019-11-07
### Added
- PHP 7.4 to ci pipeline

### Changed
- Updated all dev dependencies to latests versions
- Fixed Travis build badge
- PHPCS instead of PHPCSFixer
- PSR2 -> PSR12

## [1.0.1] - 2019-08-25
### Added
- psalm annotations for suppressing not problematic/incorrect notices
- missing PHPUnit cover annotation
- this changelog :-)

## [1.0.0] - 2019-09-08
### Added
- basic inotify stream handler
- example code
- unit tests
- static code analysis using Pslam and PHPStan

[1.1.1]: https://github.com/flow-control/react-inotify/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/flow-control/react-inotify/compare/v1.0.2...v1.1.0
[1.0.2]: https://github.com/flow-control/react-inotify/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/flow-control/react-inotify/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/flow-control/react-inotify/releases/tag/v1.0.0
