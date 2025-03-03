<?php

return [
    'driver' => env('SCOUT_DRIVER', 'meilisearch'),
    'prefix' => env('SCOUT_PREFIX', ''),
    'queue' => env('SCOUT_QUEUE', true),
    'after_commit' => false,
    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],
    'soft_delete' => false,
    'identify' => env('SCOUT_IDENTIFY', false),

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY', null),
        'index-settings' => [
            SearchIndex::class => [
                'filterableAttributes' => ['type', 'searchable_type', 'permissions'],
                'sortableAttributes' => ['created_at'],
                'searchableAttributes' => ['title', 'content']
            ]
        ],
    ],

    'searchable_models' => [
        \App\Models\ApiDoc::class,
        \App\Models\Webhook::class,
        \App\Models\File::class,
        \App\Models\FileCollection::class,
        // Add other searchable models here
    ]
]; 