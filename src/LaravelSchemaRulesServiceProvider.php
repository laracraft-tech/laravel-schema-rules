<?php

namespace LaracraftTech\LaravelSchemaRules;

use LaracraftTech\LaravelSchemaRules\Commands\GenerateRulesCommand;
use LaracraftTech\LaravelSchemaRules\Exceptions\UnsupportedDbDriverException;
use LaracraftTech\LaravelSchemaRules\Resolvers\SchemaRulesResolverInterface;
use LaracraftTech\LaravelSchemaRules\Resolvers\SchemaRulesResolverMySql;
use LaracraftTech\LaravelSchemaRules\Resolvers\SchemaRulesResolverPgSql;
use LaracraftTech\LaravelSchemaRules\Resolvers\SchemaRulesResolverSqlite;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelSchemaRulesServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-schema-rules')
            ->hasConfigFile()
            ->hasCommand(GenerateRulesCommand::class);
    }

    public function register()
    {
        parent::register();

        $this->app->bind(SchemaRulesResolverInterface::class, function ($app, $parameters) {
            $connection = config('database.default');
            $driver = config("database.connections.{$connection}.driver");

            $class = match ($driver) {
                'sqlite' => SchemaRulesResolverSqlite::class,
                'mysql' => SchemaRulesResolverMySql::class,
                'pgsql' => SchemaRulesResolverPgSql::class,
                default => throw new UnsupportedDbDriverException('This db driver is not supported: '.$driver),
            };

            return new $class(...$parameters);
        });
    }
}
