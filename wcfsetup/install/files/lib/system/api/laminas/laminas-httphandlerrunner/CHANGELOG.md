# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.5.0 - 2021-09-22


-----

### Release Notes for [1.5.0](https://github.com/laminas/laminas-httphandlerrunner/milestone/6)

Feature release (minor)

### 1.5.0

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Enhancement

 - [24: Support PHP 8.1](https://github.com/laminas/laminas-httphandlerrunner/pull/24) thanks to @Ocramius

## 1.4.0 - 2021-04-08


-----

### Release Notes for [1.4.0](https://github.com/laminas/laminas-httphandlerrunner/milestone/3)

Feature release (minor)

### 1.4.0

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Enhancement

 - [8: Initial migration to github ci workflows and psalm integration](https://github.com/laminas/laminas-httphandlerrunner/pull/8) thanks to @gsteel

## 1.3.0 - 2020-11-19

### Added

- [#7](https://github.com/laminas/laminas-httphandlerrunner/pull/7) adds support for PHP 8.0.

### Removed

- [#7](https://github.com/laminas/laminas-httphandlerrunner/pull/7) drops support for the 1.x series of laminas/laminas-diactoros.

- [#7](https://github.com/laminas/laminas-httphandlerrunner/pull/7) drops support for PHP versions prior to 7.3.


-----

### Release Notes for [1.3.0](https://github.com/laminas/laminas-httphandlerrunner/milestone/1)



### 1.3.0

- Total issues resolved: **1**
- Total pull requests resolved: **1**
- Total contributors: **2**

#### Enhancement

 - [7: Bump up PHP version constraint.](https://github.com/laminas/laminas-httphandlerrunner/pull/7) thanks to @ADmad

#### Enhancement,Help Wanted,hacktoberfest-accepted

 - [6: PHP 8.0 support](https://github.com/laminas/laminas-httphandlerrunner/issues/6) thanks to @boesing

## 1.2.0 - 2020-06-03

### Added

- Nothing.

### Changed

- [#4](https://github.com/laminas/laminas-httphandlerrunner/pull/4) adds a call to `flush()` within the `SapiStreamEmitter`, after emitting headers and the status line, but before emitting content. This change allows providing a response to the browser more quickly, allowing it to process the stream as it is pushed.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.1.0 - 2019-02-19

### Added

- [zendframework/zend-httphandlerrunner#10](https://github.com/zendframework/zend-httphandlerrunner/pull/10) adds support for laminas-diactoros v2 releases.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.2 - 2019-02-19

### Added

- [zendframework/zend-httphandlerrunner#9](https://github.com/zendframework/zend-httphandlerrunner/pull/9) adds support for PHP 7.3.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.1 - 2018-02-21

### Added

- Nothing.

### Changed

- [zendframework/zend-httphandlerrunner#2](https://github.com/zendframework/zend-httphandlerrunner/pull/2) modifies
  how the request and error response factories are composed with the
  `RequestHandlerRunner` class. In both cases, they are now encapsulated in a
  closure which also defines a return type hint, ensuring that if the factories
  produce an invalid return type, a PHP `TypeError` will be raised.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.0 - 2018-02-05

Initial stable release.

The `Laminas\HttpRequestHandler\Emitter` subcomponent was originally released as
part of two packages:

- `EmitterInterface` and the two SAPI emitter implementations were released
  previously as part of the [laminas-diactoros](https://docs.laminas.dev/laminas-daictoros)
  package.

- `EmitterStack` was previously released as part of the
  [mezzio](https://docs.mezzio.dev/mezzio/) package.

These features are mostly verbatim from that package, with minor API changes.

The `RequestHandlerRunner` was originally developed as part of version 3
development of mezzio, but extracted here for general use with
[PSR-15](https://www.php-fig.org/psr/psr-15) applications.

### Added

- Everything.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
