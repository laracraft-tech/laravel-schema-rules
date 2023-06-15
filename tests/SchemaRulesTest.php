<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use LaracraftTech\LaravelSchemaRules\Exceptions\ColumnDoesNotExistException;
use LaracraftTech\LaravelSchemaRules\Exceptions\MultipleTablesSuppliedException;
use LaracraftTech\LaravelSchemaRules\Exceptions\TableDoesNotExistException;
use LaracraftTech\LaravelSchemaRules\Resolvers\SchemaRulesResolverInterface;

it('only accepts a table argument', function () {
    $table = 'tests';
    Schema::create($table, function (Blueprint $table) {
        $table->boolean('test');
    });

    $this->expectException(\Symfony\Component\Console\Exception\InvalidArgumentException::class);

    $this->artisan("schema:generate-rules", [
        'foo' => $table,
    ]);
});

it('only accepts a --columns option', function () {
    $table = 'tests';
    Schema::create($table, function (Blueprint $table) {
        $table->boolean('test');
    });

    $this->expectException(\Symfony\Component\Console\Exception\InvalidOptionException::class);

    $this->artisan("schema:generate-rules", [
        'table' => $table,
        '--foo' => 'test',
    ]);
});

it('only handles existing tables', function () {
    $table = 'tests';
    Schema::create($table, function (Blueprint $table) {
        $table->boolean('test');
    });

    $this->expectException(TableDoesNotExistException::class);

    $this->artisan("schema:generate-rules", [
        'table' => $table.'1',
    ]);
});

it('only handles one table at a time', function () {
    $table = 'tests';
    Schema::create($table, function (Blueprint $table) {
        $table->boolean('test');
    });

    $this->expectException(MultipleTablesSuppliedException::class);

    $this->artisan("schema:generate-rules", [
        'table' => "$table,tests2",
    ]);
});

it('only handles existing table columns if supplied ', function () {
    $table = 'tests';
    Schema::create($table, function (Blueprint $table) {
        $table->boolean('test');
    });

    $this->expectException(ColumnDoesNotExistException::class);

    $this->artisan("schema:generate-rules", [
        'table' => $table,
        '--columns' => 'foo',
    ]);
});

it('generates required and null validation rules from table schema', function () {
    $table = 'tests';
    $nullableStringColumnName = 'title';

    Schema::create($table, function (Blueprint $table) use ($nullableStringColumnName) {
        $table->string($nullableStringColumnName)->nullable();
    });

    $rules = app()->make(SchemaRulesResolverInterface::class, [
        'table' => $table,
    ])->generate();

    $this->expect($rules)->toBe([
        $nullableStringColumnName => ['nullable', 'string', 'min:1'],
    ]);

    $this->artisan("schema:generate-rules $table")->assertSuccessful();
});

it('generates boolean validation rules from table schema', function () {
    $table = 'tests';
    $boolColumnName = 'is_locked';

    Schema::create($table, function (Blueprint $table) use ($boolColumnName) {
        $table->boolean($boolColumnName);
    });

    $rules = app()->make(SchemaRulesResolverInterface::class, [
        'table' => $table,
    ])->generate();

    $this->expect($rules)->toBe([
        $boolColumnName => ['required', 'boolean'],
    ]);

    $this->artisan("schema:generate-rules $table")->assertSuccessful();
});

it('generates string validation rules from table schema', function () {
    //TODO as sqlite makes no real difference between string types we may change the tests to use mysql in github action
    $table = 'tests';
    $string100ColumnName = 'title';
    $stringFirstNameColumnName = 'first_name';
    $stringLastNameColumnName = 'last_name';
    $stringDescriptionColumnName = 'description';

    Schema::create($table, function (Blueprint $table) use (
        $stringDescriptionColumnName,
        $stringLastNameColumnName,
        $stringFirstNameColumnName,
        $string100ColumnName
    ) {
        $table->string($string100ColumnName, 100);
        $table->string($stringFirstNameColumnName);
        $table->char($stringLastNameColumnName);
        $table->text($stringDescriptionColumnName);
    });

    $rules = app()->make(SchemaRulesResolverInterface::class, [
        'table' => $table,
    ])->generate();

    $this->expect($rules)->toBe([
        $string100ColumnName => ['required', 'string', 'min:1'], //sqlite has no max
        $stringFirstNameColumnName => ['required', 'string', 'min:1'], //sqlite has no max
        $stringLastNameColumnName => ['required', 'string', 'min:1'], //sqlite has no max
        $stringDescriptionColumnName => ['required', 'string', 'min:1'], //sqlite has no max
    ]);

    $this->artisan("schema:generate-rules $table")->assertSuccessful();
});

it('generates integer validation rules from table schema', function () {
    //TODO as sqlite makes no real difference between integer types we may change the tests to use mysql in github action
    $table = 'tests';
    $intColumnName = 'amount';

    Schema::create($table, function (Blueprint $table) use ($intColumnName) {
        $table->integer($intColumnName);
    });

    $rules = app()->make(SchemaRulesResolverInterface::class, [
        'table' => $table,
    ])->generate();

    $this->expect($rules)->toBe([
        $intColumnName => ['required', 'integer', 'min:-9223372036854775808', 'max:9223372036854775807'],
    ]);

    $this->artisan("schema:generate-rules $table")->assertSuccessful();
});

it('generates date validation rules from table schema', function () {
    //TODO as sqlite makes no real difference between date types we may change the tests to use mysql in github action
    $table = 'tests';
    $dateColumnName = 'published_at';
    $yearColumnName = 'year_start';
    $timeColumnName = 'time_end';

    Schema::create($table, function (Blueprint $table) use (
        $dateColumnName,
        $yearColumnName,
        $timeColumnName
    ) {
        $table->date($dateColumnName);
        $table->year($yearColumnName);
        $table->time($timeColumnName);
    });

    $rules = app()->make(SchemaRulesResolverInterface::class, [
        'table' => $table,
    ])->generate();

    $this->expect($rules)->toBe([
        $dateColumnName => ['required', 'date'],
        $yearColumnName => ['required', 'integer', 'min:-9223372036854775808', 'max:9223372036854775807'], // year is handled as an int
        $timeColumnName => ['required', 'date'],
    ]);

    $this->artisan("schema:generate-rules $table")->assertSuccessful();
});

it('generates json validation rules from table schema', function () {
    //TODO as sqlite has no specific json data type we may change the tests to use mysql in github action
    $table = 'tests';
    $jsonColumnName = 'configuration';

    Schema::create($table, function (Blueprint $table) use ($jsonColumnName) {
        $table->json($jsonColumnName);
    });

    $rules = app()->make(SchemaRulesResolverInterface::class, [
        'table' => $table,
    ])->generate();

    $this->expect($rules)->toBe([
        $jsonColumnName => ['required', 'string', 'min:1'],
    ]);

    $this->artisan("schema:generate-rules $table")->assertSuccessful();
});
