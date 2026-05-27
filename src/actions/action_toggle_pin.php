<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');
require_once(__DIR__ . '/../../database/models/Announcement.class.php');

[$session, $db] = requireAuthenticatedJsonPost();

if (!$session->isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden.']);
    exit;
}

$id = (int) ($_POST['announcement_id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid announcement.']);
    exit;
}

$pinned = Announcement::togglePin($db, $id);
echo json_encode(['success' => true, 'pinned' => $pinned]);
