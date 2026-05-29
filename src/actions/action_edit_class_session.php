<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');
require_once(__DIR__ . '/../../database/models/ClassCatalog.class.php');

[$session, $db] = requireAuthenticatedJsonPost();
if (!$session->isAdmin()) { http_response_code(403); echo json_encode(['success'=>false,'error'=>'Forbidden']); exit; }

$sessionId = (int)($_POST['session_id'] ?? 0);
$datetime  = trim($_POST['datetime'] ?? '');
$room      = trim($_POST['room'] ?? '');
$capacity  = (int)($_POST['capacity'] ?? 0);

if ($sessionId <= 0 || $datetime === '' || $room === '' || $capacity < 1) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Invalid input.']);
    exit;
}

ClassCatalog::updateSession($db, $sessionId, $datetime, $room, $capacity);
echo json_encode(['success' => true]);
