<?php

namespace LaracraftTech\LaravelSchemaRules\Resolvers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;

class SchemaRulesResolverSqlite implements SchemaRulesResolverInterface
{
    public function __construct(
        private readonly string $table,
        private readonly array  $columns = []
    ) {
        // constructor
    }

    public function generate(): array
    {
        $tableColumns = $this->getColumnsDefinitionsFromTable();

        $tableRules = [];
        foreach ($tableColumns as $column) {
            $field = $column->name;

            // If specific columns where supplied only process those...
            if (! empty($this->columns) && ! in_array($field, $this->columns)) {
                continue;
            }

            // We do not need a rule for auto increments
            if ($column->pk) {
                continue;
            }

            $tableRules[$field] = $this->generateColumnRules($column);
        }
        //dd($tableColumns);
        return $tableRules;
    }

    private function getColumnsDefinitionsFromTable()
    {
        return DB::select('PRAGMA table_info(' . $this->table . ')');
    }

    private function generateColumnRules(stdClass $column): array
    {
        $columnRules = [];
        $columnRules[] = $column->notnull ? 'required' : 'nullable' ;

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
            // I think we skip BINARY and BLOB for now
        }

        return $columnRules;
    }
}
