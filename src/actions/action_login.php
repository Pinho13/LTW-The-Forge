<?php
declare(strict_types = 1);

require_once(__DIR__ . '/../../utils/session.php');
require_once(__DIR__ . '/../../database/connection.db.php');
require_once(__DIR__ . '/../../database/User.class.php');

$session = new Session();

function redirectError(Session $session, array $formData, string $message): never {
    $session->setFormError('login', $message, $formData);
    header('Location: /src/pages/index.php?open=login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /src/pages/index.php?open=login');
    exit;
}

if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    redirectError($session, [], 'Invalid request. Please try again.');
}

$email    = trim($_POST['email']    ?? '');
$password =      $_POST['password'] ?? '';

$formData = ['email' => $email];

if ($email === '' || $password === '') {
    redirectError($session, $formData, 'All fields are required.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectError($session, $formData, 'Please enter a valid email address.');
}

$db   = getDatabaseConnection();
$user = User::verifyPassword($db, $email, $password);

if ($user === null) {
    redirectError($session, $formData, 'Invalid email or password.');
}

$session->setUser($user->user_id, $user->name, $user->role);
header('Location: /src/pages/my-account.php');
exit;
