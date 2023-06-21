<?php

namespace LaracraftTech\LaravelSchemaRules\Commands;

use Brick\VarExporter\VarExporter;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use LaracraftTech\LaravelSchemaRules\Resolvers\SchemaRulesResolverInterface;
use LaracraftTech\LaravelSchemaRules\Exceptions\ColumnDoesNotExistException;
use LaracraftTech\LaravelSchemaRules\Exceptions\MultipleTablesSuppliedException;
use LaracraftTech\LaravelSchemaRules\Exceptions\TableDoesNotExistException;
use LaracraftTech\LaravelSchemaRules\Exceptions\FailedToCreateRequestClassException;

class GenerateRulesCommand extends Command
{
    protected $signature = 'schema:generate-rules {table : The table of which you want to generate the rules}
               {--columns= : Only create rules for specific columns of the table}
               {--c|create-request : Instead of outputting the rules, create a form request class}
               {--f|force : If "create" was given, then the request class gets created even if it already exists}
               {--file= : specify the file path where to create the request class}';

    protected $description = 'Generate validation rules based on your database table schema';

    /**
     * @throws BindingResolutionException
     * @throws MultipleTablesSuppliedException
     * @throws TableDoesNotExistException
     * @throws ColumnDoesNotExistException
     * @throws FailedToCreateRequestClassException
     */
    public function handle(): int
    {
        // Arguments
        $table = (string) $this->argument('table');

        // Options
        $columns = (array) array_filter(explode(',', $this->option('columns')));
        $create = (boolean) $this->option('create-request');
        $force = (boolean) $this->option('force');
        $file = (string) $this->option('file');

        $this->checkTableAndColumns($table, $columns);

        $rules = app()->make(SchemaRulesResolverInterface::class, [
            'table' => $table,
            'columns' => $columns,
        ])->generate();

        if ($create) {
            $this->createRequest($table, $rules, $force, $file);
        } else {
            $this->createOutput($table, $rules);
        }

        return Command::SUCCESS;
    }

    private function format($rules): string
    {
        return VarExporter::export($rules, VarExporter::INLINE_SCALAR_LIST);
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

        if (! empty($missingColumns)) {
            $msg = "The following columns do not exists on the table '$table': ".implode(', ', $missingColumns);

            throw new ColumnDoesNotExistException($msg);
        }
    }

    private function createOutput(string $table, array $rules): void
    {
        $this->info("Schema-based validation rules for table \"$table\" have been generated!");
        $this->info('Copy & paste these to your controller validation or form request or where ever your validation takes place:');
        $this->line($this->format($rules));
    }

    /**
     * @throws FailedToCreateRequestClassException
     */
    private function createRequest(string $table, array $rules, bool $force = false, string $file = '')
    {
        // As a default, we create a store request based on the table name.
        if (empty($file)) {
            $file = 'Store'.Str::of($table)->singular()->ucfirst()->__toString().'Request';
        }

        Artisan::call('make:request', [
            'name' => $file,
            '--force' => $force,
        ]);

        $output = trim(Artisan::output());

        preg_match('/\[(.*?)\]/', $output, $matches);

        // The original $file we passed to the command may have changed on creation validation inside the command.
        // We take the actual path which was used to create the file!
        $actuaFile = $matches[1] ?? null;

        if ($actuaFile) {
            try {
                $fileContent = File::get($actuaFile);
                // Add spaces to indent the array in the request class file.
                $rulesFormatted = str_replace("\n", "\n        ", $this->format($rules));
                $pattern = '/(public function rules\(\): array\n\s*{\n\s*return )\[.*\](;)/s';
                $replaceContent = preg_replace($pattern, '$1'.$rulesFormatted.'$2', $fileContent);
                File::put($actuaFile, $replaceContent);
            } catch (Exception $exception) {
                throw new FailedToCreateRequestClassException($exception->getMessage());
            }
        }

        if (Str::startsWith($output, 'INFO')) {
            $this->info($output);
        } else {
            $this->error($output);
        }
    }
}
