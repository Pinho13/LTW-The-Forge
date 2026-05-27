<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');

[$session, $db] = requireAuthenticatedPost('/src/pages/page_account.php');

$current = $_POST['current_password'] ?? '';
$new     = $_POST['new_password']     ?? '';
$confirm = $_POST['confirm_password'] ?? '';

function failChangePassword(Session $session, string $msg): never {
    $session->setFormError('change_password', $msg);
    header('Location: /src/pages/page_account.php');
    exit;
}

if ($current === '') failChangePassword($session, 'Current password is required.');
if ($new !== $confirm)  failChangePassword($session, 'Passwords do not match.');

$pwErrors = [];
if (strlen($new) < 8)                      $pwErrors[] = 'at least 8 characters';
if (!preg_match('/[A-Z]/', $new))          $pwErrors[] = 'one uppercase letter';
if (!preg_match('/[a-z]/', $new))          $pwErrors[] = 'one lowercase letter';
if (!preg_match('/[0-9]/', $new))          $pwErrors[] = 'one number';
if (!preg_match('/[^a-zA-Z0-9]/', $new))   $pwErrors[] = 'one special character';
if ($pwErrors !== []) failChangePassword($session, 'Password must contain: ' . implode(', ', $pwErrors) . '.');

if (!User::verifyCurrentPassword($db, $session->getId(), $current)) failChangePassword($session, 'Current password is incorrect.');
if (User::verifyCurrentPassword($db, $session->getId(), $new))      failChangePassword($session, 'New password must differ from current password.');

User::updatePassword($db, $session->getId(), $new);
$session->setFormSuccess('change_password', 'Password updated successfully.');

header('Location: /src/pages/page_account.php');
exit;
