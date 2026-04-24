<?php
declare(strict_types=1);

$sessionPath = __DIR__ . '/../../utils/session.php';
$databasePath = __DIR__ . '/../../database/connection.db.php';
$user_classPath = __DIR__ . '/../../database/User.class.php';

require_once($sessionPath);
require_once($databasePath);
require_once($user_classPath);

$session = new Session();

function redirectLoginError(Session $session, array $formData, string $message): never
{
    $session->setFormError('login', $message, $formData);
    header('Location: /src/pages/index.php?open=login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /src/pages/index.php?open=login');
    exit;
}

if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    redirectLoginError($session, [], 'Invalid request. Please try again.');
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$formData = ['email' => $email];

if ($email === '' || $password === '') {
    redirectLoginError($session, $formData, 'All fields are required.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectLoginError($session, $formData, 'Please enter a valid email address.');
}

$db = getDatabaseConnection();
$user = User::verifyPassword($db, $email, $password);

if ($user === null) {
    redirectLoginError($session, $formData, 'Invalid email or password.');
}

$session->setUser($user->user_id, $user->name, $user->role);
$session->addMessage('success', 'Login successful.');

header('Location: /src/pages/my-account.php');
exit;