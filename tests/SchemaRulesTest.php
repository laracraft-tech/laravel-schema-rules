<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use LaracraftTech\LaravelSchemaRules\Exceptions\ColumnDoesNotExistException;
use LaracraftTech\LaravelSchemaRules\Exceptions\MultipleTablesSuppliedException;
use LaracraftTech\LaravelSchemaRules\Exceptions\TableDoesNotExistException;
use LaracraftTech\LaravelSchemaRules\Resolvers\SchemaRulesResolverInterface;
use LaracraftTech\LaravelSchemaRules\Resolvers\SchemaRulesResolverMySql;

beforeEach(function () {
    $this->tableName = 'tests';
});

afterEach(function () {
    Schema::disableForeignKeyConstraints();
    Schema::dropIfExists($this->tableName);
    Schema::enableForeignKeyConstraints();
});

it('only accepts a table argument', function () {
    Schema::create($this->tableName, function (Blueprint $table) {
        $table->boolean('test_bool');
    });

    $this->expectException(\Symfony\Component\Console\Exception\InvalidArgumentException::class);

    $this->artisan("schema:generate-rules", [
        'foo' => $this->tableName,
    ]);
});

it('only accepts a --columns option', function () {
    Schema::create($this->tableName, function (Blueprint $table) {
        $table->boolean('test_bool');
    });

    $this->expectException(\Symfony\Component\Console\Exception\InvalidOptionException::class);

    $this->artisan("schema:generate-rules", [
        'table' => $this->tableName,
        '--foo' => 'test_bool',
    ]);
});

it('only handles existing tables', function () {
    Schema::create($this->tableName, function (Blueprint $table) {
        $table->boolean('test_bool');
    });

    $this->expectException(TableDoesNotExistException::class);

    $this->artisan("schema:generate-rules", [
        'table' => $this->tableName.'1',
    ]);
});

it('only handles one table at a time', function () {
    Schema::create($this->tableName, function (Blueprint $table) {
        $table->boolean('test_bool');
    });

    $this->expectException(MultipleTablesSuppliedException::class);

    $this->artisan("schema:generate-rules", [
        'table' => $this->tableName.',tests2',
    ]);
});

it('only handles existing table columns if supplied ', function () {
    Schema::create($this->tableName, function (Blueprint $table) {
        $table->boolean('test_bool');
    });

    $this->expectException(ColumnDoesNotExistException::class);

    $this->artisan("schema:generate-rules", [
        'table' => $this->tableName,
        '--columns' => 'foo',
    ]);
});

it('generates required and null validation rules from table schema', function () {
    $stringColumnName = 'test_string';
    $stringNullableColumnName = 'test_string_nullable';

    Schema::create($this->tableName, function (Blueprint $table) use (
        $stringColumnName,
        $stringNullableColumnName
    ) {
        $table->string($stringColumnName);
        $table->string($stringNullableColumnName)->nullable();
    });

    $rules = app()->make(SchemaRulesResolverInterface::class, [
        'table' => $this->tableName,
    ])->generate();

    $this->expect($rules)->toBe([
        $stringColumnName => ['required', 'string', 'min:1', 'max:255'],
        $stringNullableColumnName => ['nullable', 'string', 'min:1', 'max:255'],
    ]);

    $this->artisan("schema:generate-rules", [
        'table' => $this->tableName,
    ])->assertSuccessful();
});

it('generates boolean validation rules from table schema', function () {
    $boolColumnName = 'test_bool';
    $boolNullableColumnName = 'test_bool_nullable';

    Schema::create($this->tableName, function (Blueprint $table) use (
        $boolColumnName,
        $boolNullableColumnName
    ) {
        $table->boolean($boolColumnName);
        $table->boolean($boolNullableColumnName)->nullable();
    });

    $rules = app()->make(SchemaRulesResolverInterface::class, [
        'table' => $this->tableName,
    ])->generate();

    $this->expect($rules)->toBe([
        $boolColumnName => ['required', 'boolean'],
        $boolNullableColumnName => ['nullable', 'boolean'],
    ]);

    $this->artisan("schema:generate-rules", [
        'table' => $this->tableName,
    ])->assertSuccessful();
});

it('generates string validation rules from table schema', function () {
    $stringColumnName = 'test_string';
    $string100ColumnName = 'test_string_100';
    $stringNullableColumnName = 'test_string_nullable';
    $charColumnName = 'test_char';
    $textColumnName = 'test_text';

    Schema::create($this->tableName, function (Blueprint $table) use (
        $stringColumnName,
        $string100ColumnName,
        $stringNullableColumnName,
        $charColumnName,
        $textColumnName
    ) {
        $table->string($stringColumnName);
        $table->string($string100ColumnName, 100);
        $table->string($stringNullableColumnName)->nullable();
        $table->char($charColumnName);
        $table->text($textColumnName);
    });

    $rules = app()->make(SchemaRulesResolverInterface::class, [
        'table' => $this->tableName,
    ])->generate();

    $this->expect($rules)->toBe([
        $stringColumnName => ['required', 'string', 'min:1', 'max:255'],
        $string100ColumnName => ['required', 'string', 'min:1', 'max:100'],
        $stringNullableColumnName => ['nullable', 'string', 'min:1', 'max:255'],
        $charColumnName => ['required', 'string', 'min:1', 'max:255'],
        $textColumnName => ['required', 'string', 'min:1'],
    ]);

    $this->artisan("schema:generate-rules", [
        'table' => $this->tableName,
    ])->assertSuccessful();
});

it('generates integer validation rules from table schema', function () {
    $tinyintColumnName = 'test_tinyint';
    $tinyintUnsignedColumnName = 'test_tinyint_unsigned';
    $smallintColumnName = 'test_smallint';
    $smallintUnsignedColumnName = 'test_smallint_unsigned';
    $mediumintColumnName = 'test_mediumint';
    $mediumintUnsignedColumnName = 'test_mediumint_unsigned';
    $intColumnName = 'test_int';
    $intUnsignedColumnName = 'test_int_unsigned';
    $bigintColumnName = 'test_bigint';
    $bigintUnsignedColumnName = 'test_bigint_unsigned';
    $bigintNullableColumnName = 'test_bigint_nullable';

    Schema::create($this->tableName, function (Blueprint $table) use (
        $tinyintColumnName,
        $tinyintUnsignedColumnName,
        $smallintColumnName,
        $smallintUnsignedColumnName,
        $mediumintColumnName,
        $mediumintUnsignedColumnName,
        $intColumnName,
        $intUnsignedColumnName,
        $bigintColumnName,
        $bigintUnsignedColumnName,
        $bigintNullableColumnName
    ) {
        $table->tinyInteger($tinyintColumnName);
        $table->unsignedTinyInteger($tinyintUnsignedColumnName);
        $table->smallInteger($smallintColumnName);
        $table->unsignedSmallInteger($smallintUnsignedColumnName);
        $table->mediumInteger($mediumintColumnName);
        $table->unsignedMediumInteger($mediumintUnsignedColumnName);
        $table->integer($intColumnName);
        $table->unsignedInteger($intUnsignedColumnName);
        $table->bigInteger($bigintColumnName);
        $table->unsignedBigInteger($bigintUnsignedColumnName);
        $table->bigInteger($bigintNullableColumnName)->nullable();
    });

    $rules = app()->make(SchemaRulesResolverInterface::class, [
        'table' => $this->tableName,
    ])->generate();

    $integerTypes = SchemaRulesResolverMySql::$integerTypes;

    $this->expect($rules)->toBe([
        $tinyintColumnName => ['required', 'integer', 'min:'.$integerTypes['tinyint']['signed'][0], 'max:'.$integerTypes['tinyint']['signed'][1]],
        $tinyintUnsignedColumnName => ['required', 'integer', 'min:'.$integerTypes['tinyint']['unsigned'][0], 'max:'.$integerTypes['tinyint']['unsigned'][1]],
        $smallintColumnName => ['required', 'integer', 'min:'.$integerTypes['smallint']['signed'][0], 'max:'.$integerTypes['smallint']['signed'][1]],
        $smallintUnsignedColumnName => ['required', 'integer', 'min:'.$integerTypes['smallint']['unsigned'][0], 'max:'.$integerTypes['smallint']['unsigned'][1]],
        $mediumintColumnName => ['required', 'integer', 'min:'.$integerTypes['mediumint']['signed'][0], 'max:'.$integerTypes['mediumint']['signed'][1]],
        $mediumintUnsignedColumnName => ['required', 'integer', 'min:'.$integerTypes['mediumint']['unsigned'][0], 'max:'.$integerTypes['mediumint']['unsigned'][1]],
        $intColumnName => ['required', 'integer', 'min:'.$integerTypes['int']['signed'][0], 'max:'.$integerTypes['int']['signed'][1]],
        $intUnsignedColumnName => ['required', 'integer', 'min:'.$integerTypes['int']['unsigned'][0], 'max:'.$integerTypes['int']['unsigned'][1]],
        $bigintColumnName => ['required', 'integer', 'min:'.$integerTypes['bigint']['signed'][0], 'max:'.$integerTypes['bigint']['signed'][1]],
        $bigintUnsignedColumnName => ['required', 'integer', 'min:'.$integerTypes['bigint']['unsigned'][0], 'max:'.$integerTypes['bigint']['unsigned'][1]],
        $bigintNullableColumnName => ['nullable', 'integer', 'min:'.$integerTypes['bigint']['signed'][0], 'max:'.$integerTypes['bigint']['signed'][1]],
    ]);

    $this->artisan("schema:generate-rules", [
        'table' => $this->tableName,
    ])->assertSuccessful();
});

it('generates numeric validation rules from table schema', function () {
    $floatColumnName = 'test_float';
    $floatUnsignedColumnName = 'test_float_unsigned';
    $doubleColumnName = 'test_double';
    $doubleUnsignedColumnName = 'test_double_unsigned';
    $decimalColumnName = 'test_decimal';
    $decimalUnsignedColumnName = 'test_decimal_unsigned';
    $decimalNullableColumnName = 'test_decimal_nullable';

    Schema::create($this->tableName, function (Blueprint $table) use (
        $floatColumnName,
        $floatUnsignedColumnName,
        $doubleColumnName,
        $doubleUnsignedColumnName,
        $decimalColumnName,
        $decimalUnsignedColumnName,
        $decimalNullableColumnName
    ) {
        $table->float($floatColumnName);
        $table->unsignedFloat($floatUnsignedColumnName);
        $table->double($doubleColumnName);
        $table->unsignedDouble($doubleUnsignedColumnName);
        $table->decimal($decimalColumnName);
        $table->unsignedDecimal($decimalUnsignedColumnName);
        $table->decimal($decimalNullableColumnName)->nullable();
    });

    $rules = app()->make(SchemaRulesResolverInterface::class, [
        'table' => $this->tableName,
    ])->generate();

    $integerTypes = SchemaRulesResolverMySql::$integerTypes;

    $this->expect($rules)->toBe([
        $floatColumnName => ['required', 'numeric'],
        $floatUnsignedColumnName => ['required', 'numeric'],
        $doubleColumnName => ['required', 'numeric'],
        $doubleUnsignedColumnName => ['required', 'numeric'],
        $decimalColumnName => ['required', 'numeric'],
        $decimalUnsignedColumnName => ['required', 'numeric'],
        $decimalNullableColumnName => ['nullable', 'numeric'],
    ]);

    $this->artisan("schema:generate-rules", [
        'table' => $this->tableName,
    ])->assertSuccessful();
});

it('generates enum and set validation rules from table schema', function () {
    $enumColumnName = 'test_enum';
    $setColumnName = 'test_set';
    $allowed = ['a', 'b', 'c'];

    Schema::create($this->tableName, function (Blueprint $table) use (
        $enumColumnName,
        $setColumnName,
        $allowed
    ) {
        $table->enum($enumColumnName, $allowed);
        $table->set($setColumnName, $allowed);
    });

    $rules = app()->make(SchemaRulesResolverInterface::class, [
        'table' => $this->tableName,
    ])->generate();

    $this->expect($rules)->toBe([
        $enumColumnName => ['required', 'string', 'in:'.implode(',', $allowed)],
        $setColumnName => ['required', 'string', 'in:'.implode(',', $allowed)],
    ]);

    $this->artisan("schema:generate-rules", [
        'table' => $this->tableName,
    ])->assertSuccessful();
});

it('generates date validation rules from table schema', function () {
    $dateColumnName = 'test_date';
    $yearColumnName = 'test_year';
    $timeColumnName = 'test_time';
    $timestampColumnName = 'test_timestamp';

    Schema::create($this->tableName, function (Blueprint $table) use (
        $dateColumnName,
        $yearColumnName,
        $timeColumnName,
        $timestampColumnName
    ) {
        $table->date($dateColumnName);
        $table->year($yearColumnName);
        $table->time($timeColumnName);
        $table->timestamp($timestampColumnName);
    });

    $rules = app()->make(SchemaRulesResolverInterface::class, [
        'table' => $this->tableName,
    ])->generate();

    $this->expect($rules)->toBe([
        $dateColumnName => ['required', 'date'],
        $yearColumnName => ['required', 'integer', 'min:1901', 'max:2155'],
        $timeColumnName => ['required', 'date'],
        $timestampColumnName => ['required', 'date', 'after_or_equal:1970-01-01 00:00:01', 'before_or_equal:2038-01-19 03:14:07'],
    ]);

    $this->artisan("schema:generate-rules", [
        'table' => $this->tableName,
    ])->assertSuccessful();
});

it('generates json validation rules from table schema', function () {
    $jsonColumnName = 'test_json';

    Schema::create($this->tableName, function (Blueprint $table) use ($jsonColumnName) {
        $table->json($jsonColumnName);
    });

    $rules = app()->make(SchemaRulesResolverInterface::class, [
        'table' => $this->tableName,
    ])->generate();

    $this->expect($rules)->toBe([
        $jsonColumnName => ['required', 'json'],
    ]);

    $this->artisan("schema:generate-rules", [
        'table' => $this->tableName,
    ])->assertSuccessful();
});

it('generates foreign key validation rules from table schema', function () {
    $foreignKeyColumnName = 'test_id';

    Schema::create($this->tableName, function (Blueprint $table) {
        $table->id();
        $table->boolean('test');
    });

    $foreignTable = 'foo';
    // make sure the table does not exist
    Schema::dropIfExists($foreignTable);
    Schema::create($foreignTable, function (Blueprint $table) use ($foreignKeyColumnName) {
        $table->id();
        $table->foreignId($foreignKeyColumnName)->constrained();
    });

    $rules = app()->make(SchemaRulesResolverInterface::class, [
        'table' => $foreignTable,
    ])->generate();

    $constrained = explode('_', $foreignKeyColumnName);
    $constrained[0] = Str::plural($constrained[0]);

    $this->expect($rules)->toBe([
        $foreignKeyColumnName => ['required', 'exists:'.implode(',', $constrained)],
    ]);

    $this->artisan("schema:generate-rules", [
        'table' => $foreignTable,
    ])->assertSuccessful();

    // make sure the table gets deleted after the test
    Schema::dropIfExists($foreignTable);
});
