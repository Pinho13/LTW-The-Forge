<?php
declare(strict_types=1);
require_once(__DIR__ . '/../../utils/session.php');
require_once(__DIR__ . '/../../database/connection.db.php');
require_once(__DIR__ . '/../../database/Enrollment.class.php');

$session = new Session();
$session->requireLogin('/src/pages/index.php?open=login');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /src/pages/my-classes.php');
    exit;
}

$enrollmentId = (int) ($_POST['enrollment_id'] ?? 0);
if ($enrollmentId <= 0) {
    header('Location: /src/pages/my-classes.php');
    exit;
}

$db = getDatabaseConnection();
Enrollment::cancelForMember($db, $enrollmentId, $session->getId());

header('Location: /src/pages/my-classes.php?tab=upcoming');
exit;
