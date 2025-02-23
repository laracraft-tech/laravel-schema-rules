# Changelog

All notable changes to `laravel-schema-rules` will be documented in this file.

## v1.5.0 - 2025-02-23

### What's Changed

* Bump dependabot/fetch-metadata from 2.2.0 to 2.3.0 by @dependabot in https://github.com/laracraft-tech/laravel-schema-rules/pull/29
* Bump aglipanci/laravel-pint-action from 2.4 to 2.5 by @dependabot in https://github.com/laracraft-tech/laravel-schema-rules/pull/30
* Laravel 12.x Compatibility by @laravel-shift in https://github.com/laracraft-tech/laravel-schema-rules/pull/31

### New Contributors

* @laravel-shift made their first contribution in https://github.com/laracraft-tech/laravel-schema-rules/pull/31

**Full Changelog**: https://github.com/laracraft-tech/laravel-schema-rules/compare/v1.4.1...v1.5.0

## v1.4.1 - 2024-08-13

### What's changed

* fixed default `schema-rules.skip_columns` by @dreammonkey in #28

## v1.4.0 - 2024-06-24

### What's changed

* Laravel 11 support by @dreammonkey

## v1.3.6 - 2023-12-06

### What's changed

* fixed Laravel 10.35 dependency issue

## v1.3.5 - 2023-11-29

### What's changed

* fixed pgsql column order

## v1.3.4 - 2023-10-25

### What's changed

- fixed min length for sqlite driver

## v1.3.3 - 2023-10-19

### What's changed

- output generated rules info text only in console mode

## v1.3.2 - 2023-08-21

### What's Changed

- Added support for jsonb on PostgreSQL by @mathieutu

## v1.3.1 - 2023-07-20

### What's Changed

- Fixed bug on `mysql` 5.8 by @giagara

## v1.3.0 - 2023-07-19

### What's Changed

- Added `skip_columns` in config (default skip `deleted_at`, `updated_at` and `created_at`) by @giagara
- Some refactoring by @giagara

### New Contributors

- @giagara made their first contribution

## v1.2.0 - 2023-06-21

### What's Changed

- Added `--create-request` flag to create **Form Request Classes**

## v1.1.0 - 2023-06-19

### What's Changed

- Support for foreigen key validation rules

## v1.0.0 - 2023-06-19

### Version 1

Automatically generate Laravel validation rules based on your database table schema!
