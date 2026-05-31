<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/page_bootstrap.php');

header('Content-Type: application/json');

$session = new Session();
if (!$session->isLoggedIn() || (!$session->isTrainer() && !$session->isAdmin())) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$sessionId = (int)($_GET['session_id'] ?? 0);
$list      = $_GET['list'] ?? 'roster'; // 'roster' | 'waitlist'

if ($sessionId <= 0 || !in_array($list, ['roster', 'waitlist'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

$db = getDatabaseConnection();

if ($list === 'roster') {
    $stmt = $db->prepare(
        "SELECT u.name, u.username
         FROM enrollment e
         JOIN user u ON u.user_id = e.member_id
         WHERE e.session_id = :sid AND e.status = 'enrolled'
         ORDER BY u.name ASC"
    );
} else {
    $stmt = $db->prepare(
        "SELECT u.name, u.username,
                (SELECT COUNT(*) + 1
                 FROM enrollment e2
                 WHERE e2.session_id = :sid2 AND e2.status = 'waitlisted'
                   AND e2.enrolled_at < e.enrolled_at) AS position
         FROM enrollment e
         JOIN user u ON u.user_id = e.member_id
         WHERE e.session_id = :sid AND e.status = 'waitlisted'
         ORDER BY e.enrolled_at ASC"
    );
    $stmt->bindValue(':sid2', $sessionId, PDO::PARAM_INT);
}

$stmt->bindValue(':sid', $sessionId, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['members' => $rows]);
