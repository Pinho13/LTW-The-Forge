<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');
require_once(__DIR__ . '/../../database/models/ClassCatalog.class.php');
require_once(__DIR__ . '/../../database/models/AdminLog.class.php');

[$session, $db] = requireAuthenticatedJsonPost();
if (!$session->isAdmin()) { http_response_code(403); echo json_encode(['success'=>false,'error'=>'Forbidden']); exit; }

$classId = (int)($_POST['class_id'] ?? 0);
if ($classId <= 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Invalid class ID.']);
    exit;
}

$nameRow = $db->prepare("SELECT name FROM class WHERE id = :id");
$nameRow->execute([':id' => $classId]);
$className = $nameRow->fetchColumn() ?: "ID $classId";

$db->prepare("DELETE FROM class_session WHERE class_id = :id")->execute([':id' => $classId]);
$db->prepare("DELETE FROM class WHERE id = :id")->execute([':id' => $classId]);
AdminLog::write($db, $session->getId(), 'DELETE', "Deleted class \"$className\"");
echo json_encode(['success' => true]);
