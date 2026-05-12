<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');

[$session, $db] = requireAuthenticatedPost('/src/pages/page_account.php');

$name     = trim($_POST['name']     ?? '');
$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email']    ?? '');
$phone    = trim($_POST['phone']    ?? '') ?: null;
$userId   = $session->getId();

$formData = ['name' => $name, 'username' => $username, 'email' => $email, 'phone' => $phone ?? ''];

function fail(Session $session, string $msg, array $data): never {
    $session->setFormError('update_account', $msg, $data);
    header('Location: /src/pages/page_account.php');
    exit;
}

if ($phone !== null) {
    if (!User::phoneHasAreaCode($phone))        fail($session, 'Phone number must include the area code (e.g. +351).', $formData);
    if (!User::phoneHasValidLength($phone))     fail($session, 'Phone number must have between 7 and 15 digits.', $formData);
    if (!User::phonePassesPortugalRules($phone)) fail($session, 'Portuguese numbers (+351) must have exactly 9 digits after the country code.', $formData);
}

if ($name === '')                               fail($session, 'Full name cannot be empty.', $formData);
if ($username === '')                           fail($session, 'Username cannot be empty.', $formData);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fail($session, 'Invalid email address.', $formData);

$existing = User::findByUsername($db, $username);
if ($existing && $existing->user_id !== $userId) fail($session, 'Username is already taken.', $formData);

$existing = User::findByEmail($db, $email);
if ($existing && $existing->user_id !== $userId) fail($session, 'Email address is already in use.', $formData);

User::update($db, $userId, $name, $username, $email, $phone);
$session->setName($name);
$session->setFormSuccess('update_account', 'Account info updated successfully.');

header('Location: /src/pages/page_account.php');
exit;
