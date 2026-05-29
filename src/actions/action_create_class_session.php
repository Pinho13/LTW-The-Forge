<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');
require_once(__DIR__ . '/../../database/models/ClassCatalog.class.php');

[$session, $db] = requireAuthenticatedJsonPost();
if (!$session->isAdmin()) { http_response_code(403); echo json_encode(['success'=>false,'error'=>'Forbidden']); exit; }

$classId  = (int)($_POST['class_id'] ?? 0);
$datetime = trim($_POST['datetime'] ?? '');
$room     = trim($_POST['room'] ?? '');
$capacity = (int)($_POST['capacity'] ?? 0);

if ($classId <= 0 || $datetime === '' || $room === '' || $capacity < 1) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Invalid input.']);
    exit;
}

$newId = ClassCatalog::createSession($db, $classId, $datetime, $room, $capacity);
echo json_encode(['success' => true, 'session_id' => $newId]);
