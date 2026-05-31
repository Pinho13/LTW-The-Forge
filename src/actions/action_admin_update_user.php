<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/page_bootstrap.php');
require_once(__DIR__ . '/../../database/models/AdminUser.class.php');
require_once(__DIR__ . '/../../database/models/TrainerProfile.class.php');
require_once(__DIR__ . '/../../database/models/AdminLog.class.php');

[$session, $db] = requireAuthenticatedPage();

if (!$session->isAdmin()) {
    $session->addMessage('error', 'Access denied.');
    header('Location: /src/pages/index.php');
    exit;
}

if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    $session->addMessage('error', 'Invalid request.');
    header('Location: /src/pages/admin-users.php');
    exit;
}

$targetId = (int)($_POST['user_id'] ?? 0);
$action   = $_POST['action'] ?? '';

if ($targetId <= 0) {
    $session->addMessage('error', 'Invalid user.');
    header('Location: /src/pages/admin-users.php');
    exit;
}

// Prevent admin from acting on themselves for destructive actions
if (in_array($action, ['ban', 'delete', 'set_role']) && $targetId === $session->getId()) {
    $session->addMessage('error', 'You cannot perform this action on your own account.');
    header('Location: /src/pages/admin-users.php');
    exit;
}

$user = AdminUser::getById($db, $targetId);
if (!$user) {
    $session->addMessage('error', 'User not found.');
    header('Location: /src/pages/admin-users.php');
    exit;
}

switch ($action) {
    case 'update_details':
        $name  = trim($_POST['name']  ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if ($name === '' || $email === '') {
            $session->addMessage('error', 'Name and email are required.');
            break;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $session->addMessage('error', 'Invalid email address.');
            break;
        }
        if (AdminUser::emailExists($db, $email, $targetId)) {
            $session->addMessage('error', 'Email already in use.');
            break;
        }

        AdminUser::updateDetails($db, $targetId, $name, $email, $phone);

        // Update trainer profile if applicable
        if ($user['role'] === 'trainer') {
            $bio   = trim($_POST['bio']            ?? '');
            $spec  = trim($_POST['specializations'] ?? '');
            $cert  = trim($_POST['certifications']  ?? '');
            TrainerProfile::upsert($db, $targetId, $bio, $spec, $cert);
        }

        AdminLog::write($db, $session->getId(), 'UPDATE', "Updated details for {$user['name']}");
        $session->addMessage('success', 'User details updated.');
        break;

    case 'set_role':
        $newRole = $_POST['role'] ?? '';
        if (!in_array($newRole, ['member', 'trainer', 'admin'])) {
            $session->addMessage('error', 'Invalid role.');
            break;
        }

        $oldRole = $user['role'];
        AdminUser::setRole($db, $targetId, $newRole);

        // Ensure trainer_profile row exists when promoting to trainer
        if ($newRole === 'trainer') {
            TrainerProfile::upsert($db, $targetId, '', '', '');
        }

        AdminLog::write($db, $session->getId(), 'ELEVATE', "Changed {$user['name']} role from $oldRole to $newRole");
        $session->addMessage('success', 'Role changed to ' . $newRole . '.');
        break;

    case 'ban':
        AdminUser::setActive($db, $targetId, false);
        AdminLog::write($db, $session->getId(), 'UPDATE', "Banned {$user['name']}");
        $session->addMessage('success', htmlspecialchars($user['name']) . ' has been banned.');
        break;

    case 'unban':
        AdminUser::setActive($db, $targetId, true);
        AdminLog::write($db, $session->getId(), 'UPDATE', "Reactivated {$user['name']}");
        $session->addMessage('success', htmlspecialchars($user['name']) . ' has been reactivated.');
        break;

    case 'update_subscription':
        $endDate = trim($_POST['end_date'] ?? '');
        if (!$endDate || !strtotime($endDate)) {
            $session->addMessage('error', 'Invalid date.');
            break;
        }
        $db->prepare(
            "UPDATE member_subscription SET end_date = :end_date
             WHERE id = (
                 SELECT id FROM member_subscription
                 WHERE member_id = :id AND status IN ('active', 'frozen')
                 ORDER BY end_date DESC LIMIT 1
             )"
        )->execute([':end_date' => $endDate, ':id' => $targetId]);
        AdminLog::write($db, $session->getId(), 'UPDATE', "Updated subscription end date for {$user['name']} to $endDate");
        $session->addMessage('success', 'Subscription end date updated.');
        break;

    case 'delete':
        AdminUser::delete($db, $targetId);
        AdminLog::write($db, $session->getId(), 'DELETE', "Deleted user {$user['name']}");
        $session->addMessage('success', 'User permanently deleted.');
        header('Location: /src/pages/admin-users.php');
        exit;

    default:
        $session->addMessage('error', 'Unknown action.');
}

$ref = $_POST['ref'] ?? '/src/pages/admin-users.php';
// Sanitize redirect - only allow our own pages
if (!str_starts_with($ref, '/src/pages/')) {
    $ref = '/src/pages/admin-users.php';
}
header('Location: ' . $ref);
exit;
