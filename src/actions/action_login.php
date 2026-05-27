<?php
declare(strict_types=1);

require_once(__DIR__ . '/action_bootstrap.php');

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

$plan = '';
if ($user->role === 'member') {
    require_once(__DIR__ . '/../../database/models/MemberSubscription.class.php');
    $plan = MemberSubscription::getActivePlanName($db, $user->user_id) ?? '';
}
$session->setUser($user->user_id, $user->name, $user->role, $plan);
$session->addMessage('toast', 'Login successful.');

header('Location: /src/pages/my-account.php');
exit;