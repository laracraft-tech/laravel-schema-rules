<?php

namespace LaracraftTech\LaravelSchemaRules\Commands;

use Doctrine\DBAL\Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Schema;
use LaracraftTech\LaravelSchemaRules\Exceptions\ColumnDoesNotExistException;
use LaracraftTech\LaravelSchemaRules\Exceptions\MultipleTablesSuppliedException;
use LaracraftTech\LaravelSchemaRules\Exceptions\TableDoesNotExistException;
use LaracraftTech\LaravelSchemaRules\Resolvers\SchemaRulesResolverInterface;

class GenerateRulesCommand extends Command
{
    protected $signature = 'schema:generate-rules {table : The table of which you want to generate the rules of}
               {--columns= : Optionally only create rules for specific columns of the table}';

    protected $description = 'Generate validation rules based on your database table schema';

    /**
     * @throws BindingResolutionException
     * @throws MultipleTablesSuppliedException
     * @throws TableDoesNotExistException
     * @throws ColumnDoesNotExistException
     */
    public function handle(): int
    {
        $table = $this->argument('table');
        $columns = array_filter(explode(',', $this->option('columns')));

        $this->checkTableAndColumns($table, $columns);

        $rulesResolver = app()->make(SchemaRulesResolverInterface::class, [
            'table' => $table,
            'columns' => $columns
        ]);

        $rules = $rulesResolver->generate();

        $this->output($table, $rules);

        return Command::SUCCESS;
    }

    private function format($rules): string
    {
        $result = "[\n";
        foreach($rules as $key => $values) {
            $result .= "    '{$key}' => [";
            $result .= implode(', ', array_map(function($value) { return "'{$value}'"; }, $values));
            $result .= "],\n";
        }
        $result .= "]";

        return $result;
    }

    /**
     * @throws MultipleTablesSuppliedException
     * @throws ColumnDoesNotExistException
     * @throws TableDoesNotExistException
     */
    private function checkTableAndColumns(string $table, array $columns = []): void
    {
        if (count($tables = array_filter(explode(',', $table))) > 1) {
            $msg = 'The command can only handle one table at a time - you gave: '.implode(', ', $tables);
            throw new MultipleTablesSuppliedException($msg);
        }

        if (! Schema::hasTable($table)) {
            throw new TableDoesNotExistException("Table '$table' not found!");
        }

        if (empty($columns)) {
            return;
        }

        $missingColumns = [];
        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                $missingColumns[] = $column;
            }
        }

        if (!empty($missingColumns)) {
            $msg = "The following columns do not exists on the table '$table': ".implode(', ', $missingColumns);
            throw new ColumnDoesNotExistException($msg);
        }
    }

    private function output(string $table, array $rules): void
    {
        $this->components->info("Schema-based validation rules for table \"$table\" generated!");

        $this->info('Copy & paste these to your controller validation or form request rules:');

        echo $this->format($rules) . PHP_EOL;
    }
}
