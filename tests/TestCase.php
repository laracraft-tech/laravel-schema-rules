<?php

namespace LaracraftTech\LaravelSchemaRules\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaracraftTech\LaravelSchemaRules\LaravelSchemaRulesServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'LaracraftTech\\LaravelSchemaRules\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelSchemaRulesServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        //        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-dynamic-model_table.php.stub';
        $migration->up();
        */
    }
}
