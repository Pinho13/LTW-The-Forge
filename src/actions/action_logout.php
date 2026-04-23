<?php
declare(strict_types=1);

require_once(__DIR__ . '/../../utils/session.php');

$session = new Session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /src/pages/my-account.php');
    exit;
}

if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    header('Location: /src/pages/my-account.php');
    exit;
}

$session->logout();
header('Location: /src/pages/index.php');
exit;