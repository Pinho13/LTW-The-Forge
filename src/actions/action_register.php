<?php
declare(strict_types = 1);

require_once(__DIR__ . '/../../utils/session.php');
require_once(__DIR__ . '/../../database/connection.db.php');
require_once(__DIR__ . '/../../database/User.class.php');

$session = new Session();

function redirectError(Session $session, array $formData, string $message): never {
    $session->setFormError('register', $message, $formData);
    header('Location: /src/pages/index.php?open=register');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /src/pages/index.php?open=register');
    exit;
}

// CSRF verification (no form data yet, nothing to restore)
if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    redirectError($session, [], 'Invalid request. Please try again.');
}

$name            = trim($_POST['name']             ?? '');
$email           = trim($_POST['email']            ?? '');
$password        =      $_POST['password']         ?? '';
$confirmPassword =      $_POST['confirm-password'] ?? '';

$formData = ['name' => $name, 'email' => $email];

if ($name === '' || $email === '' || $password === '') {
    redirectError($session, $formData, 'All fields are required.');
}

if (str_word_count($name) < 2) {
    redirectError($session, $formData, 'Please enter your full name (first and last name).');
}

$name = ucwords(mb_strtolower($name));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectError($session, $formData, 'Please enter a valid email address (e.g. user@example.com).');
}

if ($password !== $confirmPassword) {
    redirectError($session, $formData, 'Passwords do not match.');
}

$passwordErrors = [];
if (strlen($password) < 8)                $passwordErrors[] = 'at least 8 characters';
if (!preg_match('/[A-Z]/', $password))    $passwordErrors[] = 'one uppercase letter';
if (!preg_match('/[a-z]/', $password))    $passwordErrors[] = 'one lowercase letter';
if (!preg_match('/[0-9]/', $password))    $passwordErrors[] = 'one number';
if (!preg_match('/[^a-zA-Z0-9]/', $password)) $passwordErrors[] = 'one special character';

if ($passwordErrors !== []) {
    redirectError($session, $formData, 'Password must contain: ' . implode(', ', $passwordErrors) . '.');
}

$db = getDatabaseConnection();

if (User::findByEmail($db, $email) !== null) {
    redirectError($session, $formData, 'An account with that email already exists.');
}

$username = explode('@', $email)[0];

$user = User::register($db, $name, $username, $email, $password);
$session->setUser($user->user_id, $user->name, $user->role);
header('Location: /src/pages/my-account.php');
exit;
