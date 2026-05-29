<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/page_bootstrap.php');
require_once(__DIR__ . '/../../database/models/Equipment.class.php');

header('Content-Type: application/json');

$session = new Session();
if (!$session->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$db   = getDatabaseConnection();
$rows = Equipment::getAllUnitsWithStatus($db);

$out = [];
foreach ($rows as $r) {
    $out[(int)$r['id']] = [
        'is_available' => (bool)$r['is_available'],
    ];
}

echo json_encode($out);
