<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');
require_once(__DIR__ . '/../../database/models/Enrollment.class.php');

[$session, $db] = requireAuthenticatedJsonPost();

if (!$session->isPremium()) {
    http_response_code(403);
    echo json_encode(['error' => 'Classes require a Premium membership.']);
    exit;
}

$sessionId = (int) ($_POST['session_id'] ?? 0);
if ($sessionId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid session.']);
    exit;
}

// Verify the session exists and is in the future
$stmt = $db->prepare(
    "SELECT id, capacity FROM class_session
     WHERE id = :id AND datetime > datetime('now', '+1 hour')"
);
$stmt->execute([':id' => $sessionId]);
if (!$stmt->fetch()) {
    http_response_code(400);
    echo json_encode(['error' => 'Class session not found or already past.']);
    exit;
}

$status = Enrollment::enroll($db, $session->getId(), $sessionId);

$waitlistPosition = null;
if ($status === 'waitlisted') {
    $stmt = $db->prepare(
        "SELECT COUNT(*) + 1 FROM enrollment e2
         JOIN enrollment e1 ON e1.session_id = e2.session_id
         WHERE e1.member_id = :mid AND e1.session_id = :sid AND e1.status = 'waitlisted'
           AND e2.status = 'waitlisted' AND e2.enrolled_at < e1.enrolled_at"
    );
    $stmt->execute([':mid' => $session->getId(), ':sid' => $sessionId]);
    $waitlistPosition = (int) $stmt->fetchColumn();
}

echo json_encode(['success' => true, 'status' => $status, 'waitlist_position' => $waitlistPosition]);
