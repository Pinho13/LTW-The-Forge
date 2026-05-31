<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');
require_once(__DIR__ . '/../../database/models/TrainerProfile.class.php');

[$session, $db] = requireAuthenticatedPost('/src/pages/trainer-profile.php');

if (!$session->isTrainer() && !$session->isAdmin()) {
    header('Location: /src/pages/my-account.php');
    exit;
}

$bio             = trim($_POST['bio']             ?? '');
$specializations = trim($_POST['specializations'] ?? '');
$certifications  = trim($_POST['certifications']  ?? '');

if (mb_strlen($bio) > 1000 || mb_strlen($specializations) > 500 || mb_strlen($certifications) > 500) {
    $session->addMessage('error', 'One or more fields exceed the maximum length.');
    header('Location: /src/pages/trainer-profile.php');
    exit;
}

TrainerProfile::upsert($db, $session->getId(), $bio, $specializations, $certifications);

$session->addMessage('success', 'Profile updated.');
header('Location: /src/pages/trainer-profile.php');
exit;
