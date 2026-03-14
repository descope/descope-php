# Changelog

All notable changes to this project will be documented in this file.

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
