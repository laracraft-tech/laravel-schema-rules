<?php

namespace LaracraftTech\LaravelSchemaRules\Resolvers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LaracraftTech\LaravelSchemaRules\Contracts\SchemaRulesResolverInterface;
use stdClass;

class SchemaRulesResolverSqlite extends BaseSchemaRulesResolver implements SchemaRulesResolverInterface
{

    protected function getColumnsDefinitionsFromTable()
    {

        $tableColumns = collect(DB::select("PRAGMA table_info('{$this->table()}')"))->keyBy('name')->toArray();

        $foreignKeys = DB::select("PRAGMA foreign_key_list({$this->table()})");

        foreach ($foreignKeys as $foreignKey) {
            $tableColumns[$foreignKey->from]->Foreign = [
                'table' => $foreignKey->table,
                'id' => $foreignKey->to,
            ];
        }

        return $tableColumns;
    }

    protected function generateColumnRules(stdClass $column): array
    {
        $columnRules = [];
        $columnRules[] = $column->notnull ? 'required' : 'nullable' ;

        if (! empty($column->Foreign)) {
            $columnRules[] = "exists:".implode(',', $column->Foreign);

            return $columnRules;
        }

        $type = Str::of($column->type);
        switch (true) {
            case $type == 'tinyint(1)' && config('schema-rules.tinyint1_to_bool'):
                $columnRules[] = "boolean";

                break;
            case $type == 'varchar' || $type == 'text':
                $columnRules[] = "string";
                $columnRules[] = "min:".config('schema-rules.min_string');

                break;
            case $type == 'integer':
                $columnRules[] = "integer";
                $columnRules[] = "min:-9223372036854775808";
                $columnRules[] = "max:9223372036854775807";

                break;
            case $type->contains('numeric') || $type->contains('float'):
                // should we do more specific here?
                // some kind of regex validation for double, double unsigned, double(8, 2), decimal etc...?
                $columnRules[] = "numeric";

                break;
            case $type == 'date' || $type == 'time' || $type == 'datetime':
                $columnRules[] = 'date';

                break;
            default:
                // I think we skip BINARY and BLOB for now
                break;
        }

        return $columnRules;
    }
}
