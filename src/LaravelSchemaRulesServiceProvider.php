<?php

namespace LaracraftTech\LaravelSchemaRules;

use LaracraftTech\LaravelSchemaRules\Commands\GenerateRulesCommand;
use LaracraftTech\LaravelSchemaRules\Contracts\SchemaRulesResolverInterface;
use LaracraftTech\LaravelSchemaRules\Exceptions\UnsupportedDbDriverException;
use LaracraftTech\LaravelSchemaRules\Resolvers\SchemaRulesResolverMySql;
use LaracraftTech\LaravelSchemaRules\Resolvers\SchemaRulesResolverPgSql;
use LaracraftTech\LaravelSchemaRules\Resolvers\SchemaRulesResolverSqlite;
use Spatie\LaravelPackageTools\Exceptions\InvalidPackage;
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

    /**
     * @throws InvalidPackage
     */
    public function register()
    {
        parent::register();

        $this->app->bind(SchemaRulesResolverInterface::class, function ($app, $parameters) {
            $connection = config('database.default');
            $driver = config("database.connections.{$connection}.driver");

            switch ($driver) {
                case 'sqlite': $class = SchemaRulesResolverSqlite::class;

                    break;
                case 'mysql': $class = SchemaRulesResolverMySql::class;

                    break;
                case 'pgsql': $class = SchemaRulesResolverPgSql::class;

                    break;
                default: throw new UnsupportedDbDriverException('This db driver is not supported: '.$driver);
            };

            return new $class(...array_values($parameters));
        });
    }
}
