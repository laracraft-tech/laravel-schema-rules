<?php

namespace LaracraftTech\LaravelSchemaRules\Commands;

use Doctrine\DBAL\Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Schema;
use LaracraftTech\LaravelSchemaRules\Exceptions\ColumnDoesNotExistException;
use LaracraftTech\LaravelSchemaRules\Exceptions\TableDoesNotExistException;
use LaracraftTech\LaravelSchemaRules\Resolvers\SchemaRulesResolverInterface;

class GenerateRulesCommand extends Command
{
    protected $signature = 'schema:generate-rules {table : The table of which you want to generate the rules of}
               {--fields= : Optionally only create rules for specific fields of the table}';

    protected $description = 'Generate validation rules based on your database table schema';

    /**
     * @throws BindingResolutionException
     * @throws TableDoesNotExistException
     * @throws ColumnDoesNotExistException
     */
    public function handle(): int
    {
        $table = $this->argument('table');
        $fields = array_filter(explode(',', $this->option('fields')));

        $this->checkTableAndColumns($table, $fields);

        $rulesResolver = app()->make(SchemaRulesResolverInterface::class, [
            'table' => $table,
            'fields' => $fields
        ]);

        $rules = $rulesResolver->generate();

        $this->components->info("Schema-based validation rules for table \"$table\" generated!");

        $this->info('Paste these to your controller validation or form request rules:');

        echo $this->format($rules) . PHP_EOL;

        //pgsql
//        $columns = DB::select(
//            "SELECT column_name, data_type, character_maximum_length, is_nullable
//    FROM INFORMATION_SCHEMA.COLUMNS
//    WHERE table_name = :table",
//            ['table' => $table]
//        );

        return Command::SUCCESS;
    }

    private function format($rules)
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
     * @throws ColumnDoesNotExistException
     * @throws TableDoesNotExistException
     */
    private function checkTableAndColumns(string $table, array $columns = []): bool
    {
        if (! Schema::hasTable($table)) {
            throw new TableDoesNotExistException("Table '$table' not found!");
        }

        if (empty($columns)) {
            return true;
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

        return true;
    }
}
