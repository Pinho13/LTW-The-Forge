<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');
require_once(__DIR__ . '/../../database/models/Enrollment.class.php');

[$session, $db] = requireAuthenticatedJsonPost();

$enrollmentId = (int) ($_POST['enrollment_id'] ?? 0);
if ($enrollmentId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid enrollment.']);
    exit;
}

$cancelled = Enrollment::cancelForMember($db, $enrollmentId, $session->getId());
if (!$cancelled) {
    http_response_code(404);
    echo json_encode(['error' => 'Could not cancel enrollment.']);
    exit;
}

Enrollment::promoteWaitlist($db, $enrollmentId);
echo json_encode(['success' => true]);
