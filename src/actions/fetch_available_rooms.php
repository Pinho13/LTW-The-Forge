<?php
declare(strict_types=1);
header('Content-Type: application/json');
require_once(__DIR__ . '/../../utils/session.php');
require_once(__DIR__ . '/../../database/connection.db.php');

$session = new Session();
if (!$session->isLoggedIn()) { http_response_code(401); echo json_encode([]); exit; }
if (!$session->isAdmin())    { http_response_code(403); echo json_encode([]); exit; }

$db = getDatabaseConnection();

$datetime  = trim($_GET['datetime'] ?? '');
$sessionId = (int)($_GET['session_id'] ?? 0);

if ($datetime === '') {
    $rows = $db->query("SELECT name FROM class_room ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode($rows);
    exit;
}

$stmt = $db->prepare(
    "SELECT name FROM class_room
     WHERE name NOT IN (
         SELECT room FROM class_session
         WHERE datetime = :datetime
         AND (:session_id = 0 OR id != :session_id)
     )
     ORDER BY name"
);
$stmt->execute([':datetime' => $datetime, ':session_id' => $sessionId]);
echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
