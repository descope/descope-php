# Changelog

All notable changes to this project will be documented in this file.

## [0.6.6](https://github.com/descope/descope-php/compare/descope-php-v0.6.5...descope-php-v0.6.6) (2026-08-07)


### Features

* **http:** add configurable request timeout ([#131](https://github.com/descope/descope-php/issues/131)) ([fd0509f](https://github.com/descope/descope-php/commit/fd0509fb902d3bb9cdc221a70bf5e347f6ef9018))


### Bug Fixes

* **sdk:** hardening ([#126](https://github.com/descope/descope-php/issues/126)) ([e63afaf](https://github.com/descope/descope-php/commit/e63afaff5c114f23bbc19202afdab026a947287f))

## [0.6.5](https://github.com/descope/descope-php/compare/descope-php-v0.6.4...descope-php-v0.6.5) (2026-07-09)


### Features

* **sdk:** complete management SDK go-parity + fix mgmt user logout ([#123](https://github.com/descope/descope-php/issues/123)) ([6e96618](https://github.com/descope/descope-php/commit/6e966186cbf288e50db9046acef8f35edfb795da))

## [0.6.4](https://github.com/descope/descope-php/compare/descope-php-v0.6.3...descope-php-v0.6.4) (2026-07-06)


### Features

* **sdk:** implement management sdk parity between php-sdk and go-sdk ([#122](https://github.com/descope/descope-php/issues/122)) ([a74818a](https://github.com/descope/descope-php/commit/a74818a5106cf76aa9fab738d5670a1970d15774))


### Bug Fixes

* **sdk:** scope endpoint base-url state per instance to harden jwks host ([#118](https://github.com/descope/descope-php/issues/118)) ([3d8be95](https://github.com/descope/descope-php/commit/3d8be95d127183c8544d31fff2fcc09d72e246a5))
* **sdk:** verify JWT claims before returning them ([#121](https://github.com/descope/descope-php/issues/121)) ([a7864e2](https://github.com/descope/descope-php/commit/a7864e2748225b132911130c127f763a3d966bb7))
* **security:** scope JWKS and credential hosts to per-instance config ([3d8be95](https://github.com/descope/descope-php/commit/3d8be95d127183c8544d31fff2fcc09d72e246a5))

## [0.6.3](https://github.com/descope/descope-php/compare/descope-php-v0.6.2...descope-php-v0.6.3) (2026-06-22)


### Features

* **http:** also retry on transient status code 520 ([#111](https://github.com/descope/descope-php/issues/111)) ([9a4d769](https://github.com/descope/descope-php/commit/9a4d769506e12635e902e3c04be40d1f8f058aff))
* **http:** retry requests on transient error status codes ([#104](https://github.com/descope/descope-php/issues/104)) ([4c6d78c](https://github.com/descope/descope-php/commit/4c6d78cf6750dc5f0d7e0e362647cc04e0a40b31))


### Bug Fixes

* stored XSS and client-trust vulnerabilities in sample app ([#102](https://github.com/descope/descope-php/issues/102)) ([f79156b](https://github.com/descope/descope-php/commit/f79156b5c0b748e4b2bac267a0ba910fc7494fc2))

## [0.6.2](https://github.com/descope/descope-php/compare/descope-php-v0.6.1...descope-php-v0.6.2) (2026-03-14)


### Features

* add tenant role parameters to user search ([#60](https://github.com/descope/descope-php/issues/60)) ([ef278de](https://github.com/descope/descope-php/commit/ef278de18640d19a876504a02a37871abf6f4fbf))
* Added JWK Caching and Support for Laravel 11 ([#32](https://github.com/descope/descope-php/issues/32)) ([eb3f69b](https://github.com/descope/descope-php/commit/eb3f69b0bb5b8461bc56df9a6af8cf3979bd5488))
* Added SHA hashing algorithm ([#49](https://github.com/descope/descope-php/issues/49)) ([c6efbce](https://github.com/descope/descope-php/commit/c6efbce984f60ad8d70aac791192349c661f7d62))
* Debug logger ([#74](https://github.com/descope/descope-php/issues/74)) ([6223274](https://github.com/descope/descope-php/commit/62232743a6ca0d0ec862a142e64adf4ec22a7328))
* Proper Error Handling with SDK ([#85](https://github.com/descope/descope-php/issues/85)) ([d744475](https://github.com/descope/descope-php/commit/d74447514b0edb3addb379dc6b16d8c9081f6475))


### Bug Fixes

* **deps:** update dependency paragonie/constant_time_encoding to v2.8.2 ([#71](https://github.com/descope/descope-php/issues/71)) ([58dc914](https://github.com/descope/descope-php/commit/58dc9148a8e18949bc865c3ccf2fa2b748e1e502))
* Fixed Function Definitions and Access Modifiers ([#31](https://github.com/descope/descope-php/issues/31)) ([bf29968](https://github.com/descope/descope-php/commit/bf299683559aac3e641a5e3814345de2caf452aa))

## [0.5.0] - 2024-01-15

### Features

* Proper Error Handling with SDK
* NullCache fallback disables JWKS caching entirely when APCu unavailable

### Security

* Update dependency phpunit/phpunit to v9.6.33 (security fix)

### Chore

* Update actions/checkout to v6
* Update actions/checkout to v5
