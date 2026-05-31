<?php
declare(strict_types=1);
date_default_timezone_set('Europe/Lisbon');
require_once(__DIR__ . '/../../utils/deny_direct_access.php');
require_once(__DIR__ . '/../../utils/session.php');
require_once(__DIR__ . '/../../database/connection.db.php');
require_once(__DIR__ . '/../../database/models/AdminLog.class.php');

header('Content-Type: application/json');

$session = new Session();

if (!$session->isLoggedIn() || !$session->isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}

$raw  = file_get_contents('php://input');
$body = json_decode($raw, true);

if (!is_array($body) || !$session->verifyCsrfToken($body['csrf_token'] ?? null)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token.']);
    exit;
}

$units = $body['units'] ?? [];
if (!is_array($units)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload.']);
    exit;
}

$db = getDatabaseConnection();

try {
    $db->beginTransaction();

    $db->exec("UPDATE equipment_unit SET map_x = NULL, map_y = NULL, map_w = NULL, map_h = NULL, rotation = 0");

    $update = $db->prepare(
        "UPDATE equipment_unit
         SET map_x = :x, map_y = :y, map_w = :w, map_h = :h, rotation = :r
         WHERE id = :id"
    );

    $insert = $db->prepare(
        "INSERT INTO equipment_unit (equipment_id, map_x, map_y, map_w, map_h, rotation)
         VALUES (:eq_id, :x, :y, :w, :h, :r)"
    );

    foreach ($units as $u) {
        $id   = $u['id'] ?? null;
        $eqId = filter_var($u['eq_id'] ?? null, FILTER_VALIDATE_INT);
        $x    = filter_var($u['x']     ?? null, FILTER_VALIDATE_INT);
        $y    = filter_var($u['y']     ?? null, FILTER_VALIDATE_INT);
        $w    = filter_var($u['w']     ?? null, FILTER_VALIDATE_INT);
        $h    = filter_var($u['h']     ?? null, FILTER_VALIDATE_INT);
        $rot  = filter_var($u['rotation'] ?? 0, FILTER_VALIDATE_INT);

        if ($x === false || $y === false || $w === false || $h === false) continue;
        if ($w <= 0 || $h <= 0) continue;
        if (!in_array($rot, [0, 90, 180, 270], true)) $rot = 0;

        if ($id !== null && (int)$id > 0) {
            $update->execute([':id' => (int)$id, ':x' => $x, ':y' => $y, ':w' => $w, ':h' => $h, ':r' => $rot]);
        } elseif ($eqId !== false && $eqId > 0) {
            $insert->execute([':eq_id' => $eqId, ':x' => $x, ':y' => $y, ':w' => $w, ':h' => $h, ':r' => $rot]);
        }
    }

    $db->commit();
    AdminLog::write($db, $session->getId(), 'UPDATE', 'Saved equipment map layout');
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save layout.']);
}
