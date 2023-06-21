# Laravel Schema Rules

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laracraft-tech/laravel-schema-rules.svg?style=flat-square)](https://packagist.org/packages/laracraft-tech/laravel-useful-traits)
[![Tests](https://github.com/laracraft-tech/laravel-schema-rules/actions/workflows/run-tests.yml/badge.svg?branch=main)](https://github.com/laracraft-tech/laravel-useful-traits/actions/workflows/run-tests.yml)
[![Check & fix styling](https://github.com/laracraft-tech/laravel-schema-rules/actions/workflows/fix-php-code-style-issues.yml/badge.svg?branch=main)](https://github.com/laracraft-tech/laravel-useful-traits/actions/workflows/fix-php-code-style-issues.yml)
[![License](https://img.shields.io/packagist/l/laracraft-tech/laravel-schema-rules.svg?style=flat-square)](https://packagist.org/packages/laracraft-tech/laravel-useful-traits)
<!--[![Total Downloads](https://img.shields.io/packagist/dt/laracraft-tech/laravel-schema-rules.svg?style=flat-square)](https://packagist.org/packages/laracraft-tech/laravel-useful-traits)-->

Automatically generate basic Laravel validation rules based on your database table schema!
Use these as a starting point to fine-tune and optimize your validation rules as needed. 

## Installation

You can install the package via composer:

```bash
composer require laracraft-tech/laravel-schema-rules --dev
```

Then publish the config file with:

```bash
php artisan vendor:publish --tag="schema-rules-config"
```

## ToC

- [`Generate rules for the whole table`](#generate-rules-for-the-whole-table)
- [`Generate rules for specific columns`](#generate-rules-for-specific-columns)
- [`Generate Form Request Class`](#generate-form-request-class)

## Usage

Let's say you've migrated this fictional table:

````php
Schema::create('persons', function (Blueprint $table) {
    $table->id();
    $table->string('first_name', 100);
    $table->string('last_name', 100);
    $table->string('email');
    $table->foreignId('address_id')->constrained();
    $table->text('bio')->nullable();
    $table->enum('gender', ['m', 'f', 'd']);
    $table->date('birth');
    $table->year('graduated');
    $table->float('body_size');
    $table->unsignedTinyInteger('children_count')->nullable();
    $table->integer('account_balance');
    $table->unsignedInteger('net_income');
    $table->boolean('send_newsletter')->nullable();
});
````

### Generate rules for the whole table

Now if you run:

`php artisan schema:generate-rules persons`

You'll get:
```
Schema-based validation rules for table "persons" have been generated!
Copy & paste these to your controller validation or form request or where ever your validation takes place:
[
    'first_name' => ['required', 'string', 'min:1', 'max:100'],
    'last_name' => ['required', 'string', 'min:1', 'max:100'],
    'email' => ['required', 'string', 'min:1', 'max:255'],
    'address_id' => ['required', 'exists:addresses,id'],
    'bio' => ['nullable', 'string', 'min:1'],
    'gender' => ['required', 'string', 'in:m,f,d'],
    'birth' => ['required', 'date'],
    'graduated' => ['required', 'integer', 'min:1901', 'max:2155'],
    'body_size' => ['required', 'numeric'],
    'children_count' => ['nullable', 'integer', 'min:0', 'max:255'],
    'account_balance' => ['required', 'integer', 'min:-2147483648', 'max:2147483647'],
    'net_income' => ['required', 'integer', 'min:0', 'max:4294967295'],
    'send_newsletter' => ['nullable', 'boolean']
]
```

As you may have noticed the float-column `body_size`, just gets generated to `['required', 'numeric']`.
Proper rules for `float`, `decimal` and `double`, are not yet implemented! 

### Generate rules for specific columns

You can also explicitly specify the columns:

`php artisan schema:generate-rules persons --columns first_name,last_name,email`

Which gives you:
````
Schema-based validation rules for table "persons" have been generated!
Copy & paste these to your controller validation or form request or where ever your validation takes place:
[
    'first_name' => ['required', 'string', 'min:1', 'max:100'],
    'last_name' => ['required', 'string', 'min:1', 'max:100'],
    'email' => ['required', 'string', 'min:1', 'max:255']
]
````

### Generate Form Request Class

Optionally, you can add a `--create-request` or `-c` flag,
which will create a form request class with the generated rules for you!

```` bash
# creates app/Http/Requests/StorePersonRequest.php (store request is the default)
php artisan schema:generate-rules persons --create-request 

# creates/overwrites app/Http/Requests/StorePersonRequest.php
php artisan schema:generate-rules persons --create-request --force
 
# creates app/Http/Requests/UpdatePersonRequest.php
php artisan schema:generate-rules persons --create-request --file UpdatePersonRequest

# creates app/Http/Requests/Api/V1/StorePersonRequest.php
php artisan schema:generate-rules persons --create-request --file Api\\V1\\StorePersonRequest

# creates/overwrites app/Http/Requests/Api/V1/StorePersonRequest.php (using shortcuts)
php artisan schema:generate-rules persons -cf --file Api\\V1\\StorePersonRequest
````

## Supported Drivers

Currently, the supported database drivers are `MySQL`, `PostgreSQL`, and `SQLite`.

Please note, since each driver supports different data types and range specifications,
the validation rules generated by this package may vary depending on the database driver you are using.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Zacharias Creutznacher](https://github.com/laracraft-tech)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
