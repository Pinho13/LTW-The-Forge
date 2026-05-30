<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');
require_once(__DIR__ . '/../../database/models/ClassCatalog.class.php');
require_once(__DIR__ . '/../../database/models/AdminLog.class.php');

[$session, $db] = requireAuthenticatedJsonPost();
if (!$session->isAdmin()) { http_response_code(403); echo json_encode(['success'=>false,'error'=>'Forbidden']); exit; }

$name        = trim($_POST['name'] ?? '');
$typeId      = (int)($_POST['type_id'] ?? 0);
$description = trim($_POST['description'] ?? '');
$duration    = (int)($_POST['duration_minutes'] ?? 0);
$intensity   = (int)($_POST['intensity'] ?? 0);
$trainerId   = ($_POST['trainer_id'] ?? '') !== '' ? (int)$_POST['trainer_id'] : null;

if ($name === '' || $typeId <= 0 || $duration < 1 || $intensity < 1 || $intensity > 5) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Invalid input.']);
    exit;
}

$newId = ClassCatalog::createClass($db, $name, $typeId, $description, $duration, $intensity, $trainerId);
AdminLog::write($db, $session->getId(), 'CREATE', "Created class \"$name\"");
echo json_encode(['success' => true, 'class_id' => $newId]);
