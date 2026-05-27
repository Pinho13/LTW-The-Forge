<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');
require_once(__DIR__ . '/../../database/models/Enrollment.class.php');

[$session, $db] = requireAuthenticatedJsonPost();

$sessionId = (int) ($_POST['session_id'] ?? 0);
if ($sessionId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid session.']);
    exit;
}

// Find the enrollment by session+member
$stmt = $db->prepare(
    "SELECT id FROM enrollment
     WHERE session_id = :sid AND member_id = :mid AND status NOT IN ('cancelled')"
);
$stmt->execute([':sid' => $sessionId, ':mid' => $session->getId()]);
$enrollmentId = $stmt->fetchColumn();

if (!$enrollmentId) {
    http_response_code(404);
    echo json_encode(['error' => 'Enrollment not found.']);
    exit;
}

$cancelled = Enrollment::cancelForMember($db, (int) $enrollmentId, $session->getId());
if ($cancelled) {
    Enrollment::promoteWaitlist($db, (int) $enrollmentId);
}

echo json_encode(['success' => true]);
