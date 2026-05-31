<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');
require_once(__DIR__ . '/../../database/models/Announcement.class.php');
require_once(__DIR__ . '/../../database/models/AdminLog.class.php');

[$session, $db] = requireAuthenticatedJsonPost();

if (!$session->isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden.']);
    exit;
}

$id     = (int)($_POST['announcement_id'] ?? 0);
$action = $_POST['action'] ?? 'toggle';
$swapId = (int)($_POST['swap_id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid announcement.']);
    exit;
}

$titleStmt = $db->prepare("SELECT title FROM announcement WHERE id = :id");
$titleStmt->execute([':id' => $id]);
$annoTitle = $titleStmt->fetchColumn() ?: "ID $id";

if ($action === 'swap' && $swapId > 0) {
    $swapStmt = $db->prepare("SELECT title FROM announcement WHERE id = :id");
    $swapStmt->execute([':id' => $swapId]);
    $swapTitle = $swapStmt->fetchColumn() ?: "ID $swapId";
    Announcement::swapPin($db, $id, $swapId);
    AdminLog::write($db, $session->getId(), 'UPDATE', "Swapped pinned post: unpinned \"$annoTitle\", pinned \"$swapTitle\"");
    echo json_encode(['success' => true, 'pinned' => false]);
    exit;
}

$row = $db->prepare("SELECT pinned FROM announcement WHERE id = :id");
$row->execute([':id' => $id]);
$current = $row->fetchColumn();

if ($current === false) {
    http_response_code(404);
    echo json_encode(['error' => 'Announcement not found.']);
    exit;
}

if ((bool)$current === false) {
    // Pinning: unpin any existing, then pin this one
    Announcement::pin($db, $id);
    AdminLog::write($db, $session->getId(), 'UPDATE', "Pinned announcement \"$annoTitle\"");
    echo json_encode(['success' => true, 'pinned' => true]);
    exit;
}

// Unpinning: must swap — return list of candidates
$unpinned = Announcement::getAllUnpinned($db);
echo json_encode(['success' => false, 'must_swap' => true, 'candidates' => $unpinned, 'unpin_id' => $id]);
