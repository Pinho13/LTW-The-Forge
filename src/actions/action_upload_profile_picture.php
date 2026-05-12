<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');

[$session, ] = requireAuthenticatedPost('/src/pages/page_account.php');

$userId  = $session->getId();
$destDir = __DIR__ . '/../../database/profile_pictures';
$dest    = $destDir . '/' . $userId . '.png';

function failUpload(Session $session, string $msg): never {
    $session->setFormError('upload_pfp', $msg);
    header('Location: /src/pages/page_account.php');
    exit;
}

$file = $_FILES['photo'] ?? null;

if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    failUpload($session, 'Upload failed. Please try again.');
}

if ($file['size'] > 5 * 1024 * 1024) {
    failUpload($session, 'Image must be smaller than 5 MB.');
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($file['tmp_name']);
$allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

if (!in_array($mime, $allowed, true)) {
    failUpload($session, 'Only JPEG, PNG, WEBP, and GIF images are allowed.');
}

$src = imagecreatefromstring(file_get_contents($file['tmp_name']));
if ($src === false) {
    failUpload($session, 'Could not process the image. Please try a different file.');
}

$w    = imagesx($src);
$h    = imagesy($src);
$side = min($w, $h);
$x    = (int)(($w - $side) / 2);
$y    = (int)(($h - $side) / 2);

$out = imagecreatetruecolor(512, 512);
imagecopyresampled($out, $src, 0, 0, $x, $y, 512, 512, $side, $side);
imagedestroy($src);

if (!is_dir($destDir)) {
    mkdir($destDir, 0755, true);
}

imagepng($out, $dest);
imagedestroy($out);

header('Location: /src/pages/page_account.php');
exit;
