<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');
require_once(__DIR__ . '/../../database/models/Equipment.class.php');

[$session, $db] = requireAuthenticatedJsonPost();

if (!$session->isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied.']);
    exit;
}

$unitId = (int) ($_POST['unit_id'] ?? 0);
$status = $_POST['status'] ?? '';

if ($unitId <= 0 || !in_array($status, ['available', 'maintenance'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
    exit;
}

Equipment::setUnitStatus($db, $unitId, $status);
echo json_encode(['success' => true, 'status' => $status]);
