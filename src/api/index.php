<?php
declare(strict_types=1);
require_once(__DIR__ . '/api_bootstrap.php');

apiJson([
    'name'    => 'The Forge REST API',
    'version' => '1.0',
    'base_url' => '/src/api',
    'endpoints' => [
        [
            'path'        => '/classes.php',
            'method'      => 'GET',
            'description' => 'List upcoming class sessions.',
            'parameters'  => [
                'type'    => 'Filter by class type name (optional)',
                'trainer' => 'Filter by trainer user ID (optional)',
                'date'    => 'Filter by date in YYYY-MM-DD format (optional)',
            ],
        ],
        [
            'path'        => '/trainers.php',
            'method'      => 'GET',
            'description' => 'List all active trainer profiles.',
            'parameters'  => [
                'id' => 'Get a single trainer by user ID, including upcoming classes (optional)',
            ],
        ],
        [
            'path'        => '/equipment.php',
            'method'      => 'GET',
            'description' => 'List all equipment with current availability.',
            'parameters'  => [],
        ],
    ],
]);
