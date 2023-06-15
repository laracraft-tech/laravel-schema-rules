<?php

namespace LaracraftTech\LaravelSchemaRules\Resolvers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;

class SchemaRulesResolverPgSql implements SchemaRulesResolverInterface
{
    private string $table;
    private array $columns;
    private array $integerTypes = [];

    public function __construct(string $table, array $columns = [])
    {
        $this->table = $table;
        $this->columns = $columns;

        $this->integerTypes = [
            'smallint' => ['-32768', '32767'],
            'integer' => ['-2147483648', '2147483647'],
            'bigint' => ['-9223372036854775808', '9223372036854775807'],
        ];
    }

    public function generate(): array
    {
        $tableColumns = $this->getColumnsDefinitionsFromTable();

        $tableRules = [];
        foreach ($tableColumns as $column) {
            $field = $column->column_name;

            // If specific columns where supplied only process those...
            if (! empty($this->columns) && ! in_array($field, $this->columns)) {
                continue;
            }

            // We do not need a rule for auto increments
            if (Str::contains($column->column_default, 'nextval')) {
                continue;
            }

            $tableRules[$field] = $this->generateColumnRules($column);
        }

        return $tableRules;
    }

    private function getColumnsDefinitionsFromTable()
    {
        return DB::select(
            "
            SELECT column_name, data_type, character_maximum_length, is_nullable, column_default
                FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = :table",
            ['table' => $this->table]
        );
    }

    private function generateColumnRules(stdClass $column): array
    {
        $columnRules = [];
        $columnRules[] = $column->is_nullable === "YES" ? 'nullable' : 'required' ;

        $type = Str::of($column->data_type);
        switch (true) {
            case $type == 'boolean':
                $columnRules[] = "boolean";

                break;
            case $type->contains('char'):
                $columnRules[] = "string";
                $columnRules[] = "min:".config('schema-rules.min_string');
                $columnRules[] = "max:".$column->character_maximum_length;

                break;
            case $type == 'text':
                $columnRules[] = "string";
                $columnRules[] = "min:".config('schema-rules.min_string');

                break;
            case $type->contains('int'):
                $columnRules[] = "integer";
                $columnRules[] = "min:".$this->integerTypes[$type->__toString()][0];
                $columnRules[] = "max:".$this->integerTypes[$type->__toString()][1];

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
}
