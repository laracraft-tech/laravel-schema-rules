<?php

namespace LaracraftTech\LaravelSchemaRules\Resolvers;

use LaracraftTech\LaravelSchemaRules\Contracts\SchemaRulesResolverInterface;
use stdClass;

abstract class BaseSchemaRulesResolver implements SchemaRulesResolverInterface
{
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

        $skip_columns = config('schema-rules.skip_columns', []);

        $tableRules = [];
        foreach ($tableColumns as $column) {
            $field = $this->getField($column);

            // If specific columns where supplied only process those...
            if (! empty($this->columns()) && ! in_array($field, $this->columns())) {
                continue;
            }

            // If column should be skipped
            if (in_array($field, $skip_columns)) {
                continue;
            }

            // We do not need a rule for auto increments
            if ($this->isAutoIncrement($column)) {
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

    abstract protected function isAutoIncrement($column): bool;

    abstract protected function getField($column): string;

    abstract protected function getColumnsDefinitionsFromTable();

    abstract protected function generateColumnRules(stdClass $column): array;
}
