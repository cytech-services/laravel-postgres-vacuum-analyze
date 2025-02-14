<?php

namespace CytechServices\LaravelPostgresVacuumAnalyze\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LaravelPostgresVacuumAnalyzeCommand extends Command implements Isolatable
{
    public $signature = 'db:vacuum-analyze';

    public $description = 'Vacuum and anaylyze all/specific tables for specific connections for specific schemas in the database.';

    private $errors = [
        'connections' => [],
        'tables' => [],
    ];

    public function handle(): int
    {
        $config = config('postgres-vacuum-analyze.connections');

        // dump($config);

        // Create progress bar for the connection(s)
        $connectionProgressBar = $this->output->createProgressBar(count($config));
        $connectionProgressBar->setFormat("[%bar%] %current%/%max% - Connection: %message%\n");

        // Loop through the connections
        foreach ($config as $connection => $schemas) {
            // Set the connections progress bar to the current connection name and advance
            $connectionProgressBar->setMessage($connection);
            $connectionProgressBar->advance();

            try {
                // Set the connection
                $db = DB::connection($connection);

                // Create a progress bar for the schema(s)
                $schemaProgressBar = $this->output->createProgressBar(count($schemas));
                $schemaProgressBar->setFormat("[%bar%] %current%/%max% - Schema: %message%\n");

                // Loop through the schemas
                foreach ($schemas as $schema => $tableConfig) {
                    // Set the schemas progress bar to the current schema name and advance
                    $schemaProgressBar->setMessage($schema);
                    $schemaProgressBar->advance();

                    // Try catch the loop through the tables
                    try {
                        // Check if there are the same tables in both include and exclude.
                        // Throw an exception if there are.
                        if (!empty($tableConfig['include']) && !empty($tableConfig['exclude'])) {
                            $intersect = array_intersect($tableConfig['include'], $tableConfig['exclude']);
                            if (!empty($intersect)) {
                                throw new \Exception("The tables '" . implode("', '", $intersect) . "' are in both the include and exclude arrays.");
                            }
                        }


                        if (empty($tableConfig['include']) && empty($tableConfig['exclude'])) {
                            // Get all tables in the schema
                            $tables = $db->select('SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = ?', [$schema]);
                        } elseif (!empty($tableConfig['include']) && !empty($tableConfig['exclude'])) {
                            // Get all tables in the schema and filter by the included and excluded tables
                            $tables = $db->select('SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = ? AND tablename IN (?) AND tablename NOT IN (?)', [
                                $schema,
                                implode(',', $tableConfig['include']),
                                implode(',', $tableConfig['exclude']),
                            ]);
                        } elseif (empty($tableConfig['include'])) {
                            // Get all tables in the schema and filter by the excluded tables
                            $tables = $db->select('SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = ? AND tablename NOT IN (?)', [
                                $schema,
                                implode(',', $tableConfig['exclude']),
                            ]);
                        } elseif (empty($tableConfig['exclude'])) {
                            // Get all tables in the schema and filter by the included tables
                            $tables = $db->select('SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = ? AND tablename IN (?)', [
                                $schema,
                                implode(',', $tableConfig['include']),
                            ]);
                        }

                        // Create a progress bar for the table(s)
                        $tableProgressBar = $this->output->createProgressBar(count($tables));
                        $tableProgressBar->setFormat("[%bar%] %current%/%max% - Table: %message%\n");

                        // Loop through the tables
                        foreach ($tables as $table) {
                            // Set the tables progress bar to the current table name and advance
                            $tableProgressBar->setMessage($table->tablename);
                            $tableProgressBar->advance();

                            // Log the current action
                            if (config('postgres-vacuum-analyze.log_verbose')) {
                                Log::info("Vacuuming and analyzing table '{$table->tablename}' in schema '{$schema}' in connection '{$connection}'.");
                            }

                            // Vacuum and analyze the table
                            $db->statement("VACUUM ANALYZE {$table->tablename}");
                        }
                    } catch (\Exception $e) {
                        if (config('postgres-vacuum-analyze.log_errors')) {
                            Log::error("Error vacuuming and analyzing tables for schema '{$schema}' in connection '{$connection}'.");
                            Log::error($e->getMessage());
                        }

                        $this->errors['tables'][] = [
                            'message' => "Error vacuuming and analyzing tables for schema '{$schema}' in connection '{$connection}'.",
                            'exception' => $e->getMessage(),
                        ];

                        continue;
                    }
                }
            } catch (\Exception $e) {
                if (config('postgres-vacuum-analyze.log_errors')) {
                    Log::error("Connection '{$connection}' does not exist.");
                    Log::error($e->getMessage());
                }

                $this->errors['connections'][] = [
                    'message' => "Connection '{$connection}' does not exist.",
                    'exception' => $e->getMessage(),
                ];

                continue;
            }
        }

        // Finish all progress bars
        $connectionProgressBar->finish();
        if (isset($schemaProgressBar)) $schemaProgressBar->finish();
        if (isset($tableProgressBar)) $tableProgressBar->finish();

        // Check if there were any errors, display them and return a failure
        if (count($this->errors['connections']) > 0 || count($this->errors['tables']) > 0) {
            $this->line('');

            foreach ($this->errors['connections'] as $error) {
                $this->error($error['message']);
                $this->error($error['exception']);
                $this->line('');
            }

            foreach ($this->errors['tables'] as $error) {
                $this->error($error['message']);
                $this->error($error['exception']);
                $this->line('');
            }

            return self::FAILURE;
        }

        // Log the completion
        if (config('postgres-vacuum-analyze.log_verbose')) {
            Log::info('Vacuuming and analyzing tables completed.');
        }

        // Return a success
        return self::SUCCESS;
    }
}
