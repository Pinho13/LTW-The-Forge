<?php
declare(strict_types=1);
require_once(__DIR__ . '/../../utils/page_bootstrap.php');
require_once(__DIR__ . '/../../database/models/MemberSubscription.class.php');

[$session, $db] = requireAuthenticatedPage();

if (!$session->isMember() || $session->isPremium()) {
    header('Location: /src/pages/my-account.php');
    exit;
}

if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    $session->addMessage('error', 'Invalid request.');
    header('Location: /src/pages/premium-only.php');
    exit;
}

$memberId = $session->getId();

$stmt = $db->prepare(
    'UPDATE member_subscription SET plan_id = 2
     WHERE id = (
         SELECT id FROM member_subscription
         WHERE member_id = :member_id AND status IN (\'active\', \'frozen\')
         ORDER BY start_date DESC LIMIT 1
     )'
);
$stmt->execute([':member_id' => $memberId]);

$session->setUser($memberId, $session->getName(), 'member', 'premium');

$session->addMessage('success', 'You are now a Premium member. Enjoy your new features!');
header('Location: /src/pages/classes.php');
exit;
