<?php
declare(strict_types=1);
require_once(__DIR__ . '/../../utils/page_bootstrap.php');
require_once(__DIR__ . '/../../database/models/Equipment.class.php');

header('Content-Type: application/json');

$session = new Session();
if (!$session->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized.']);
    exit;
}

$db     = getDatabaseConnection();
$unitId = (int) ($_GET['unit_id'] ?? 0);
$date   = trim($_GET['date'] ?? '');

if ($unitId <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameters.']);
    exit;
}

$stmt = $db->prepare(
    "SELECT start_datetime, end_datetime
     FROM equipment_reservation
     WHERE unit_id = :uid
       AND date(start_datetime) = :date
     ORDER BY start_datetime ASC"
);
$stmt->execute([':uid' => $unitId, ':date' => $date]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$slots = [];
foreach ($rows as $r) {
    $s = strtotime($r['start_datetime']);
    $e = strtotime($r['end_datetime']);
    $slots[] = [
        'start_min' => (int)date('H', $s) * 60 + (int)date('i', $s),
        'end_min'   => (int)date('H', $e) * 60 + (int)date('i', $e),
    ];
}

echo json_encode(['slots' => $slots]);
