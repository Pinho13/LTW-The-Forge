<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');

[$session, $db] = requireAuthenticatedPost('/src/pages/page_account.php');

if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    $session->addMessage('error', 'Invalid request.');
    header('Location: /src/pages/page_account.php');
    exit;
}

if (!$session->isMember() || $session->isFrozen()) {
    header('Location: /src/pages/page_account.php');
    exit;
}

$isPremium = $session->isPremium();
$newPlanId = $isPremium ? 1 : 2;
$newPlan   = $isPremium ? 'basic' : 'premium';

$stmt = $db->prepare(
    'UPDATE member_subscription SET plan_id = :plan_id
     WHERE id = (
         SELECT id FROM member_subscription
         WHERE member_id = :member_id AND status = \'active\'
         ORDER BY start_date DESC LIMIT 1
     )'
);
$stmt->execute([':plan_id' => $newPlanId, ':member_id' => $session->getId()]);

$session->setUser($session->getId(), $session->getName(), 'member', $newPlan);

$msg = $isPremium
    ? 'You have been downgraded to the Basic plan.'
    : 'You have been upgraded to Premium. Enjoy your new features!';
$session->addMessage('success', $msg);

header('Location: /src/pages/page_account.php');
exit;
