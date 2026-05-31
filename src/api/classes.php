<?php
declare(strict_types=1);
require_once(__DIR__ . '/api_bootstrap.php');

$db = getDatabaseConnection();

$type     = isset($_GET['type'])    ? trim($_GET['type'])    : null;
$trainer  = isset($_GET['trainer']) ? (int) $_GET['trainer'] : null;
$date     = isset($_GET['date'])    ? trim($_GET['date'])    : null;

$sql = "SELECT cs.id AS session_id,
               c.id  AS class_id,
               c.name,
               ct.name AS type,
               c.description,
               c.duration_minutes,
               c.intensity,
               cs.datetime,
               cs.room,
               cs.capacity,
               COUNT(CASE WHEN e.status = 'enrolled' THEN 1 END) AS enrolled,
               u.user_id AS trainer_id,
               u.name AS trainer_name
        FROM class_session cs
        JOIN class c ON c.id = cs.class_id
        LEFT JOIN class_type ct ON ct.id = c.type_id
        LEFT JOIN user u ON u.user_id = c.trainer_id
        LEFT JOIN enrollment e ON e.session_id = cs.id
        WHERE cs.datetime >= datetime('now')";

$params = [];

if ($type !== null && $type !== '') {
    $sql .= " AND ct.name = :type";
    $params[':type'] = $type;
}
if ($trainer !== null) {
    $sql .= " AND c.trainer_id = :trainer";
    $params[':trainer'] = $trainer;
}
if ($date !== null && $date !== '') {
    $sql .= " AND date(cs.datetime) = :date";
    $params[':date'] = $date;
}

$sql .= " GROUP BY cs.id ORDER BY cs.datetime ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$sessions = array_map(fn($r) => [
    'session_id'       => (int) $r['session_id'],
    'class_id'         => (int) $r['class_id'],
    'name'             => $r['name'],
    'type'             => $r['type'],
    'description'      => $r['description'],
    'duration_minutes' => (int) $r['duration_minutes'],
    'intensity'        => (int) $r['intensity'],
    'datetime'         => $r['datetime'],
    'room'             => $r['room'],
    'capacity'         => (int) $r['capacity'],
    'enrolled'         => (int) $r['enrolled'],
    'spots_left'       => max(0, (int) $r['capacity'] - (int) $r['enrolled']),
    'trainer'          => $r['trainer_id'] ? [
        'id'   => (int) $r['trainer_id'],
        'name' => $r['trainer_name'],
    ] : null,
], $rows);

apiJson(['count' => count($sessions), 'sessions' => $sessions]);
