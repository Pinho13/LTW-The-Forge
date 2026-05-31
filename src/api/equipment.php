<?php
declare(strict_types=1);
require_once(__DIR__ . '/api_bootstrap.php');
require_once(__DIR__ . '/../../database/models/Equipment.class.php');

$db = getDatabaseConnection();

$equipment = Equipment::getAllWithUnits($db);

apiJson([
    'count'     => count($equipment),
    'equipment' => array_map(fn($e) => [
        'id'              => (int) $e['id'],
        'name'            => $e['name'],
        'type'            => $e['type'],
        'description'     => $e['description'],
        'total_units'     => (int) $e['total_units'],
        'available_units' => (int) $e['available_units'],
    ], $equipment),
]);
