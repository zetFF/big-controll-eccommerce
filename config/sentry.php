<?php

return [
    'dsn' => env('SENTRY_LARAVEL_DSN', null),

    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.1),

    'send_default_pii' => true,

    'breadcrumbs' => [
        'logs' => true,
        'sql_queries' => true,
        'sql_bindings' => true,
        'queue_info' => true,
        'command_info' => true,
    ],

    'release' => trim(exec('git log --pretty="%h" -n1 HEAD')),

    'enable' => false,
]; 