<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');
require_once(__DIR__ . '/../../database/models/ClassCatalog.class.php');
require_once(__DIR__ . '/../../database/models/AdminLog.class.php');

[$session, $db] = requireAuthenticatedJsonPost();
if (!$session->isAdmin()) { http_response_code(403); echo json_encode(['success'=>false,'error'=>'Forbidden']); exit; }

$sessionId = (int)($_POST['session_id'] ?? 0);
if ($sessionId <= 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Invalid session ID.']);
    exit;
}

$stmt = $db->prepare("SELECT c.name, cs.datetime FROM class_session cs JOIN class c ON c.id = cs.class_id WHERE cs.id = :id");
$stmt->execute([':id' => $sessionId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$label = $row ? "\"{$row['name']}\" on {$row['datetime']}" : "session ID $sessionId";

ClassCatalog::deleteSession($db, $sessionId);
AdminLog::write($db, $session->getId(), 'DELETE', "Deleted session $label");
echo json_encode(['success' => true]);
