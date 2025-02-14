<?php

namespace CytechServices\LaravelPostgresVacuumAnalyze\Commands;

use Illuminate\Console\Command;

class LaravelPostgresVacuumAnalyzeCommand extends Command
{
    public $signature = 'laravel-postgres-vacuum-analyze';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
