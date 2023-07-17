<?php

namespace LaracraftTech\LaravelSchemaRules\Resolvers;

use LaracraftTech\LaravelSchemaRules\Contracts\SchemaRulesResolverInterface;
use stdClass;

class AbstractSchemaRulesResolver implements SchemaRulesResolverInterface
{

    private array $default_skippable_columns = ['created_at', 'updated_at', 'deleted_at'];

    private string $table;
    private array $columns;

    public function __construct(string $table, array $columns = [])
    {
        $this->table = $table;
        $this->columns = $columns;
    }

    public function generate(): array
    {
        $tableColumns = $this->getColumnsDefinitionsFromTable();

        $skip_columns = array_merge($this->default_skippable_columns, config('schema-rules.skip_columns'));

        $tableRules = [];
        foreach ($tableColumns as $column) {
            $field = $column->Field;

            // If specific columns where supplied only process those...
            if (! empty($this->columns()) && ! in_array($field, $this->columns())) {
                continue;
            }

            // If column should be skipped
            if (in_array($field, $skip_columns)) {
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

    protected function table()
    {
        return $this->table;
    }

    protected function columns()
    {
        return $this->columns;
    }

    protected function getColumnsDefinitionsFromTable()
    {
        throw new \BadMethodCallException("Method not implemented in concrete class");
    }

    protected function generateColumnRules(stdClass $column): array
    {
        throw new \BadMethodCallException("Method not implemented in concrete class");
    }
}
