<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');

[$session, $db] = requireAuthenticatedPost('/src/pages/page_account.php');

if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    $session->addMessage('error', 'Invalid request.');
    header('Location: /src/pages/page_account.php');
    exit;
}

MemberSubscription::unpause($db, $session->getId());
$session->setFrozen(false);
$session->addMessage('success', 'Your membership has been reactivated.');
header('Location: /src/pages/page_account.php');
exit;
