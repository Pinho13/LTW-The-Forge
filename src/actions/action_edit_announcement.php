<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');
require_once(__DIR__ . '/../../database/models/Announcement.class.php');
require_once(__DIR__ . '/../../database/models/AdminLog.class.php');

[$session, $db] = requireAuthenticatedPost('/src/pages/news.php');

if (!$session->isAdmin()) {
    $session->addMessage('error', 'Only admins can edit announcements.');
    header('Location: /src/pages/news.php');
    exit;
}

$id       = (int)($_POST['announcement_id'] ?? 0);
$title    = trim($_POST['title'] ?? '');
$body     = trim($_POST['body'] ?? '');
$type     = trim($_POST['type'] ?? 'Gym News');
$readTime = max(1, (int)($_POST['read_time'] ?? 1));
$pinned   = !empty($_POST['pinned']);

if ($id <= 0 || $title === '' || $body === '') {
    $session->addMessage('error', 'Title and body are required.');
    header('Location: /src/pages/news.php');
    exit;
}

$imageName = null;
$file = $_FILES['image'] ?? null;
if ($file && $file['error'] === UPLOAD_ERR_OK) {
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    $mime    = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $allowed)) {
        $session->addMessage('error', 'Image must be JPG, PNG or WebP.');
        header('Location: /src/pages/news.php');
        exit;
    }
    $ext       = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime];
    $imageName = bin2hex(random_bytes(8)) . '.' . $ext;
    $dest      = __DIR__ . '/../../database/assets/announcements/' . $imageName;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        $session->addMessage('error', 'Failed to save image.');
        header('Location: /src/pages/news.php');
        exit;
    }
}

Announcement::update($db, $id, $title, $body, $type, $readTime, $pinned, $imageName);
AdminLog::write($db, $session->getId(), 'UPDATE', "Updated announcement \"$title\"");
$session->addMessage('success', 'Announcement updated.');
header('Location: /src/pages/news.php');
exit;
