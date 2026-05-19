<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');

[$session, $db] = requireAuthenticatedPost('/src/pages/page_account.php');

$duration = (int) ($_POST['duration'] ?? 0);

if (!in_array($duration, MemberSubscription::ALLOWED_PAUSE_DAYS, true)) {
    header('Location: /src/pages/page_account.php');
    exit;
}

MemberSubscription::pause($db, $session->getId(), $duration);

header('Location: /src/pages/page_account.php');
exit;
