<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');

[$session, $db] = requireAuthenticatedPost('/src/pages/page_account.php');

if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    $session->addMessage('error', 'Invalid request.');
    header('Location: /src/pages/page_account.php');
    exit;
}

$duration = (int) ($_POST['duration'] ?? 0);

if (!in_array($duration, MemberSubscription::ALLOWED_PAUSE_DAYS, true)) {
    $session->addMessage('error', 'Invalid pause duration.');
    header('Location: /src/pages/page_account.php');
    exit;
}

MemberSubscription::pause($db, $session->getId(), $duration);
$session->setFrozen(true);
$session->addMessage('success', 'Your membership has been paused.');
header('Location: /src/pages/page_account.php');
exit;
