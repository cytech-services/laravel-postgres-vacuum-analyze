<?php

namespace CytechServices\LaravelPostgresVacuumAnalyze;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use CytechServices\LaravelPostgresVacuumAnalyze\Commands\LaravelPostgresVacuumAnalyzeCommand;

class LaravelPostgresVacuumAnalyzeServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-postgres-vacuum-analyze')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_postgres_vacuum_analyze_table')
            ->hasCommand(LaravelPostgresVacuumAnalyzeCommand::class);
    }
}
