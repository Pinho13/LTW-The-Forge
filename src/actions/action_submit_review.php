<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');
require_once(__DIR__ . '/../../database/models/Review.class.php');

[$session, $db] = requireAuthenticatedPost('/src/pages/my-classes.php?tab=past');

$classId = (int)($_POST['class_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if ($classId <= 0 || $rating < 1 || $rating > 5) {
    $session->addMessage('error', 'Invalid review data.');
    header('Location: /src/pages/my-classes.php?tab=past');
    exit;
}

Review::upsert($db, $session->getId(), $classId, $rating, $comment);

$session->addMessage('success', 'Your review has been saved.');
header('Location: /src/pages/my-classes.php?tab=past');
exit;
