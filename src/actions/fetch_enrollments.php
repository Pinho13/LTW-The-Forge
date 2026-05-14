<?php
declare(strict_types=1);
require_once(__DIR__ . '/../../utils/session.php');
require_once(__DIR__ . '/../../database/connection.db.php');
require_once(__DIR__ . '/../../database/Enrollment.class.php');
require_once(__DIR__ . '/../templates/enrollment.tpl.php');

header('Content-Type: application/json');

$session = new Session();
if (!$session->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$tab = $_GET['tab'] ?? '';
if (!in_array($tab, ['upcoming', 'past'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid tab']);
    exit;
}

$offset = max(0, (int)($_GET['offset'] ?? 0));
$db = getDatabaseConnection();
$memberId = $session->getId();

$rows = $tab === 'upcoming'
    ? Enrollment::getUpcomingForMember($db, $memberId, $offset)
    : Enrollment::getPastForMember($db, $memberId, $offset);

$hasMore = count($rows) > 30;
$rows = array_slice($rows, 0, 30);

ob_start();
foreach ($rows as $row) drawEnrollmentItem($row, $tab);
$html = ob_get_clean();

echo json_encode(['html' => $html, 'hasMore' => $hasMore]);
