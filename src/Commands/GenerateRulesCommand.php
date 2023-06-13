<?php

namespace LaracraftTech\LaravelSchemaRules\Commands;

use Doctrine\DBAL\Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use LaracraftTech\LaravelSchemaRules\Resolvers\SchemaRulesResolverInterface;

class GenerateRulesCommand extends Command
{
    protected $signature = 'schema:generate-rules {table : The table of which you want to generate the rules of}
               {--fields= : Optionally only create rules for specific fields of the table}';

    protected $description = 'Generate validation rules based on your database table schema';

    /**
     * @throws Exception
     * @throws BindingResolutionException
     * @throws ExceptionInterface
     */
    public function handle(): int
    {
        $table = $this->argument('table');

        $rulesResolver = app()->make(SchemaRulesResolverInterface::class, [
            'table' => $table,
            'fields' => array_filter(explode(',', $this->option('fields')))
        ]);

        $rules = $rulesResolver->generate();

        $this->components->info("Schema-based validation rules for table \"$table\" generated!");

        $this->info('Paste these to your controller validation or form request rules:');

        echo $this->transform($rules) . PHP_EOL;

        //pgsql
//        $columns = DB::select(
//            "SELECT column_name, data_type, character_maximum_length, is_nullable
//    FROM INFORMATION_SCHEMA.COLUMNS
//    WHERE table_name = :table",
//            ['table' => $table]
//        );

        return Command::SUCCESS;
    }

    private function transform($rules)
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
}
