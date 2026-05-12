<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');

[$session, $db] = requireAuthenticatedPost('/src/pages/page_account.php');

$current = $_POST['current_password'] ?? '';
$new     = $_POST['new_password']     ?? '';
$confirm = $_POST['confirm_password'] ?? '';

function fail(Session $session, string $msg): never {
    $session->setFormError('change_password', $msg);
    header('Location: /src/pages/page_account.php');
    exit;
}

if ($current === '') fail($session, 'Current password is required.');
if ($new !== $confirm)  fail($session, 'Passwords do not match.');
if (strlen($new) < 8)  fail($session, 'New password must be at least 8 characters.');

if (!User::verifyCurrentPassword($db, $session->getId(), $current)) fail($session, 'Current password is incorrect.');
if (User::verifyCurrentPassword($db, $session->getId(), $new))      fail($session, 'New password must differ from current password.');

User::updatePassword($db, $session->getId(), $new);
$session->setFormSuccess('change_password', 'Password updated successfully.');

header('Location: /src/pages/page_account.php');
exit;
