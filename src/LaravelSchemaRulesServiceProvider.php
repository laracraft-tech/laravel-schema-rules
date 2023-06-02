<?php

namespace LaracraftTech\LaravelSchemaRules;

use LaracraftTech\LaravelSchemaRules\Commands\GenerateRulesCommand;
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
}
