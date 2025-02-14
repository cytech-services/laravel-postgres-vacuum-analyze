<?php

// config for CytechServices/LaravelPostgresVacuumAnalyze
return [

    /**
     * The connections to use for the vacuum analyze command.
     */
    'connections' => [
        /**
         * The connection name.
         * By default, the connection name is the default postgres connection.
         */
        'pgsql' => [
            /**
             * The schema name.
             * By default, the schema name is the public schema.
             */
            'public' => [
                /**
                 * A array list of tables to include in the vacuum analyze command.
                 * If no tables are specified, all tables will be included.
                 */
                'include' => [
                    // 
                ],

                /**
                 * A array list of tables to exclude in the vacuum analyze command.
                 */
                'exclude' => [
                    // 
                ],
            ],
        ],
    ],

    // Enable or disable the logging of errors.
    'log_errors' => true,

    // Enable or disable verbose logging.
    'log_verbose' => true,
];
