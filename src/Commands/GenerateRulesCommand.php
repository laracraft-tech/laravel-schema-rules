<?php

namespace LaracraftTech\LaravelSchemaRules\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GenerateRulesCommand extends Command
{
    protected $signature = 'schema-rules:generate {table : The table of which you want to generate the rules of.}
               {--columns : Optionally only create rules for passed columns of the table!}';

    protected $description = 'Generate Laravel validation rules based on your database table schema types!';

    public function handle(): int
    {
    
    }
}
