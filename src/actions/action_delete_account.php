<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');

[$session, $db] = requireAuthenticatedPost('/src/pages/page_account.php');

$password = $_POST['password'] ?? '';

if ($password === '') {
    $session->setFormError('delete_account', 'Password is required.');
    header('Location: /src/pages/page_account.php');
    exit;
}

if (!User::verifyCurrentPassword($db, $session->getId(), $password)) {
    $session->setFormError('delete_account', 'Incorrect password.');
    header('Location: /src/pages/page_account.php');
    exit;
}

User::delete($db, $session->getId());
$session->logout();

header('Location: /src/pages/index.php');
exit;
