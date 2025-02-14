<?php

namespace CytechServices\LaravelPostgresVacuumAnalyze\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \CytechServices\LaravelPostgresVacuumAnalyze\LaravelPostgresVacuumAnalyze
 */
class LaravelPostgresVacuumAnalyze extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \CytechServices\LaravelPostgresVacuumAnalyze\LaravelPostgresVacuumAnalyze::class;
    }
}
