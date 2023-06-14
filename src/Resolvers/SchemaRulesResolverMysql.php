<?php

namespace LaracraftTech\LaravelSchemaRules\Resolvers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;

class SchemaRulesResolverMysql implements SchemaRulesResolverInterface
{
    private array $integerTypes = [];

    public function __construct(
        private readonly string $table,
        private readonly array  $columns = []
    ) {
        $this->integerTypes = [
            'tinyint' => [
                'unsigned' => [config('schema-rules.min_int_unsigned'), '255'],
                'signed' => ['-128', '127'],
            ],
            'smallint' => [
                'unsigned' => [config('schema-rules.min_int_unsigned'), '65535'],
                'signed' => ['-32768', '32767'],
            ],
            'mediumint' => [
                'unsigned' => [config('schema-rules.min_int_unsigned'), '16777215'],
                'signed' => ['-8388608', '8388607'],
            ],
            'int' => [
                'unsigned' => [config('schema-rules.min_int_unsigned'), '4294967295'],
                'signed' => ['-2147483648', '2147483647'],
            ],
            'bigint' => [
                'unsigned' => [config('schema-rules.min_int_unsigned'), '18446744073709551615'],
                'signed' => ['-9223372036854775808', '9223372036854775807'],
            ],
        ];
    }

    public function generate(): array
    {
        $tableColumns = $this->getColumnsDefinitionsFromTable();

        $tableRules = [];
        foreach ($tableColumns as $column) {
            $field = $column->Field;

            // If specific columns where supplied only process those...
            if (! empty($this->columns) && ! in_array($field, $this->columns)) {
                continue;
            }

            // We do not need a rule for auto increments
            if ($column->Extra === 'auto_increment') {
                continue;
            }

            $tableRules[$field] = $this->generateColumnRules($column);
        }

        return $tableRules;
    }

    private function getColumnsDefinitionsFromTable()
    {
        return DB::select('SHOW COLUMNS FROM '.$this->table);
    }

    private function generateColumnRules(stdClass $column): array
    {
        $columnRules = [];
        $columnRules[] = $column->Null === "YES" ? 'nullable' : 'required' ;

        $type = Str::of($column->Type);
        switch (true) {
            case $type == 'tinyint(1)' && config('schema-rules.tinyint1_to_bool'):
                $columnRules[] = "boolean";

                break;
            case $type->contains('char'):
                $columnRules[] = "string";
                $columnRules[] = "min:".config('schema-rules.min_string');
                $columnRules[] = "max:".filter_var($type, FILTER_SANITIZE_NUMBER_INT);

                break;
            case $type == 'text':
                $columnRules[] = "string";
                $columnRules[] = "min:".config('schema-rules.min_string');

                break;
            case $type->contains('int'):
                $sign = ($type->contains('unsigned')) ? 'unsigned' : 'signed' ;
                $intType = $type->before(' unsigned')->__toString();
                $columnRules[] = "integer";
                $columnRules[] = "min:".$this->integerTypes[$intType][$sign][0];
                $columnRules[] = "max:".$this->integerTypes[$intType][$sign][1];

                break;
                //TODO double, decimal
            case $type->contains('enum'):
                preg_match_all("/'([^']*)'/", $type, $matches);
                $columnRules[] = "in:".implode(',', $matches[1]);

                break;
        }

        return $columnRules;
    }
}
