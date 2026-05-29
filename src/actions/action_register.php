<?php
declare(strict_types=1);

require_once(__DIR__ . '/action_bootstrap.php');

$session = new Session();

function redirectRegisterError(Session $session, array $formData, string $message): never
{
    $session->setFormError('register', $message, $formData);
    header('Location: /src/pages/index.php?open=register');
    exit;
}

function generateUniqueUsername(PDO $db, string $email): string
{
    $baseUsername = strtolower(explode('@', $email)[0]);
    $baseUsername = preg_replace('/[^a-z0-9._-]/', '', $baseUsername) ?? 'member';

    if ($baseUsername === '') {
        $baseUsername = 'member';
    }

    $username = $baseUsername;
    $counter = 1;

    while (User::findByUsername($db, $username) !== null) {
        $username = $baseUsername . $counter;
        $counter++;
    }

    return $username;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /src/pages/index.php?open=register');
    exit;
}

if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    redirectRegisterError($session, [], 'Invalid request. Please try again.');
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm-password'] ?? '';
$plan = trim($_POST['membership'] ?? 'Basic');

$formData = [
    'name' => $name,
    'email' => $email,
];

if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
    redirectRegisterError($session, $formData, 'All fields are required.');
}

if (strpos(trim($name), ' ') === false) {
    redirectRegisterError($session, $formData, 'Please enter your full name.');
}

$name = ucwords(mb_strtolower($name));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectRegisterError($session, $formData, 'Please enter a valid email address.');
}

if ($password !== $confirmPassword) {
    redirectRegisterError($session, $formData, 'Passwords do not match.');
}

$passwordErrors = [];

if (strlen($password) < 8) {
    $passwordErrors[] = 'at least 8 characters';
}
if (!preg_match('/[A-Z]/', $password)) {
    $passwordErrors[] = 'one uppercase letter';
}
if (!preg_match('/[a-z]/', $password)) {
    $passwordErrors[] = 'one lowercase letter';
}
if (!preg_match('/[0-9]/', $password)) {
    $passwordErrors[] = 'one number';
}
if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
    $passwordErrors[] = 'one special character';
}

if ($passwordErrors !== []) {
    redirectRegisterError(
        $session,
        $formData,
        'Password must contain: ' . implode(', ', $passwordErrors) . '.'
    );
}

if (!in_array($plan, ['Basic', 'Premium'], true)) {
    redirectRegisterError($session, $formData, 'Please select a valid membership tier.');
}

$db = getDatabaseConnection();

if (User::findByEmail($db, $email) !== null) {
    redirectRegisterError($session, $formData, 'An account with that email already exists.');
}

$username = generateUniqueUsername($db, $email);

$user = User::register($db, $name, $username, $email, $password, $plan);

$session->setUser($user->user_id, $user->name, $user->role, $plan);
$session->addMessage('success', 'Registration successful.');

header('Location: /src/pages/my-account.php');
exit;