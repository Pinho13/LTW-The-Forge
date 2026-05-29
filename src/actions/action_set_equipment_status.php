<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');
require_once(__DIR__ . '/../../database/models/Equipment.class.php');
require_once(__DIR__ . '/../../database/models/AdminLog.class.php');

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
$label = $db->prepare("SELECT COALESCE(eu.identifier, e.name) FROM equipment_unit eu JOIN equipment e ON e.id=eu.equipment_id WHERE eu.id=:id");
$label->execute([':id' => $unitId]);
$unitLabel = $label->fetchColumn() ?: "unit $unitId";
AdminLog::write($db, $session->getId(), 'UPDATE', "Marked $unitLabel $status");
echo json_encode(['success' => true, 'status' => $status]);
