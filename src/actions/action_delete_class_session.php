<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');
require_once(__DIR__ . '/../../database/models/ClassCatalog.class.php');

[$session, $db] = requireAuthenticatedJsonPost();
if (!$session->isAdmin()) { http_response_code(403); echo json_encode(['success'=>false,'error'=>'Forbidden']); exit; }

$sessionId = (int)($_POST['session_id'] ?? 0);
if ($sessionId <= 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Invalid session ID.']);
    exit;
}

ClassCatalog::deleteSession($db, $sessionId);
echo json_encode(['success' => true]);
