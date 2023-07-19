<?php

namespace LaracraftTech\LaravelSchemaRules\Resolvers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LaracraftTech\LaravelSchemaRules\Contracts\SchemaRulesResolverInterface;
use stdClass;

class SchemaRulesResolverPgSql extends BaseSchemaRulesResolver implements SchemaRulesResolverInterface
{
    public static array $integerTypes = [
        'smallint' => ['-32768', '32767'],
        'integer' => ['-2147483648', '2147483647'],
        'bigint' => ['-9223372036854775808', '9223372036854775807'],
    ];

    protected function getColumnsDefinitionsFromTable()
    {
        $databaseName = config('database.connections.mysql.database');
        $tableName = $this->table();

        $tableColumns = collect(DB::select(
            "
            SELECT column_name, data_type, character_maximum_length, is_nullable, column_default
                FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = :table",
            ['table' => $tableName]
        ))->keyBy('column_name')->toArray();

        $foreignKeys = DB::select("
            SELECT
                kcu.column_name,
                ccu.table_name AS foreign_table_name,
                ccu.column_name AS foreign_column_name
            FROM
                information_schema.table_constraints AS tc
                JOIN information_schema.key_column_usage AS kcu
                  ON tc.constraint_name = kcu.constraint_name
                  AND tc.table_schema = kcu.table_schema
                JOIN information_schema.constraint_column_usage AS ccu
                  ON ccu.constraint_name = tc.constraint_name
                  AND ccu.table_schema = tc.table_schema
            WHERE tc.constraint_type = 'FOREIGN KEY' AND tc.table_name=? AND tc.table_catalog=?
        ", [$tableName, $databaseName]);

        foreach ($foreignKeys as $foreignKey) {
            $tableColumns[$foreignKey->column_name]->Foreign = [
                'table' => $foreignKey->foreign_table_name,
                'id' => $foreignKey->foreign_column_name,
            ];
        }

        return $tableColumns;
    }

    protected function generateColumnRules(stdClass $column): array
    {
        $columnRules = [];
        $columnRules[] = $column->is_nullable === "YES" ? 'nullable' : 'required' ;

        if (! empty($column->Foreign)) {
            $columnRules[] = "exists:".implode(',', $column->Foreign);

            return $columnRules;
        }

        $type = Str::of($column->data_type);
        switch (true) {
            case $type == 'boolean':
                $columnRules[] = "boolean";

                break;
            case $type->contains('char'):
                $columnRules[] = "string";
                $columnRules[] = "min:".config('schema-rules.string_min_length');
                $columnRules[] = "max:".$column->character_maximum_length;

                break;
            case $type == 'text':
                $columnRules[] = "string";
                $columnRules[] = "min:".config('schema-rules.string_min_length');

                break;
            case $type->contains('int'):
                $columnRules[] = "integer";
                $columnRules[] = "min:" . self::$integerTypes[$type->__toString()][0];
                $columnRules[] = "max:" . self::$integerTypes[$type->__toString()][1];

                break;
            case $type->contains('double') ||
            $type->contains('decimal') ||
            $type->contains('numeric') ||
            $type->contains('real'):
                // should we do more specific here?
                // some kind of regex validation for double, double unsigned, double(8, 2), decimal etc...?
                $columnRules[] = "numeric";

                break;
                // unfortunately, it's not so easy in pgsql to find out if a column is an enum
                //            case $type->contains('enum') || $type->contains('set'):
                //                preg_match_all("/'([^']*)'/", $type, $matches);
                //                $columnRules[] = "in:".implode(',', $matches[1]);
                //
                //                break;
            case $type == 'date' || $type->contains('time '):
                $columnRules[] = 'date';

                break;
            case $type == 'json':
                $columnRules[] = 'json';

                break;
            default:
                // I think we skip BINARY and BLOB for now
                break;
        }

        return $columnRules;
    }

    protected function isAutoIncrement($column): bool
    {
        return Str::contains($column->column_default, 'nextval');
    }

    protected function getField($column): string
    {
        return $column->column_name;
    }
}
