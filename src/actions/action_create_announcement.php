<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');
require_once(__DIR__ . '/../../database/models/Announcement.class.php');

[$session, $db] = requireAuthenticatedPost('/src/pages/news.php');

if (!$session->isAdmin()) {
    $session->addMessage('error', 'Only admins can post announcements.');
    header('Location: /src/pages/news.php');
    exit;
}

$title    = trim($_POST['title'] ?? '');
$body     = trim($_POST['body'] ?? '');
$type     = trim($_POST['type'] ?? 'Gym News');
$readTime = max(1, (int)($_POST['read_time'] ?? 1));
$pinned   = !empty($_POST['pinned']);

if ($title === '' || $body === '') {
    $session->addMessage('error', 'Title and body are required.');
    header('Location: /src/pages/news.php');
    exit;
}

Announcement::create($db, $session->getId(), $title, $body, $pinned, $type, $readTime);
$session->addMessage('success', 'Announcement published.');
header('Location: /src/pages/news.php');
exit;
