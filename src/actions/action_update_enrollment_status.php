<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');
require_once(__DIR__ . '/../../database/models/Enrollment.class.php');

[$session, $db] = requireAuthenticatedJsonPost();

$enrollmentId = (int)($_POST['enrollment_id'] ?? 0);
$status = $_POST['status'] ?? '';

if ($enrollmentId <= 0 || !in_array($status, ['completed', 'missed'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

$now = date('Y-m-d H:i:s');
$updated = Enrollment::updateStatus($db, $enrollmentId, $session->getId(), $status, $now);

if (!$updated) {
    http_response_code(409);
    echo json_encode(['error' => 'Could not update enrollment status']);
    exit;
}

echo json_encode(['success' => true]);
