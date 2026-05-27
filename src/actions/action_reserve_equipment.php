<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');
require_once(__DIR__ . '/../../database/models/Equipment.class.php');

[$session, $db] = requireAuthenticatedJsonPost();

if (!$session->isMember()) {
    http_response_code(403);
    echo json_encode(['error' => 'Only members can reserve equipment.']);
    exit;
}

$unitId = (int) ($_POST['unit_id'] ?? 0);
$date   = trim($_POST['date'] ?? '');
$start  = trim($_POST['start_time'] ?? '');
$end    = trim($_POST['end_time'] ?? '');

if ($unitId <= 0 || $date === '' || $start === '' || $end === '') {
    http_response_code(400);
    echo json_encode(['error' => 'All fields are required.']);
    exit;
}

$startDt = $date . ' ' . $start . ':00';
$endDt   = $date . ' ' . $end   . ':00';

if ($endDt <= $startDt) {
    http_response_code(400);
    echo json_encode(['error' => 'End time must be after start time.']);
    exit;
}

if ($startDt < date('Y-m-d H:i:s')) {
    http_response_code(400);
    echo json_encode(['error' => 'Cannot reserve in the past.']);
    exit;
}

$diffMins = (strtotime($endDt) - strtotime($startDt)) / 60;
if ($diffMins > 120) {
    http_response_code(400);
    echo json_encode(['error' => 'Reservations are limited to 2 hours.']);
    exit;
}

if (Equipment::hasConflict($db, $unitId, $startDt, $endDt)) {
    http_response_code(409);
    echo json_encode(['error' => 'That unit is already reserved for the selected time slot.']);
    exit;
}

Equipment::reserve($db, $session->getId(), $unitId, $startDt, $endDt);
echo json_encode(['success' => true]);
