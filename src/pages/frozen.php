<?php
declare(strict_types=1);
require_once(__DIR__ . '/../../utils/page_bootstrap.php');
require_once(__DIR__ . '/../../database/models/MemberSubscription.class.php');

[$session, $db] = requireAuthenticatedPage();

if (!$session->isFrozen()) {
    header('Location: /src/pages/my-account.php');
    exit;
}

$frozenUntil = MemberSubscription::getFrozenUntil($db, $session->getId());
$untilLabel  = $frozenUntil ? date('M j, Y', strtotime($frozenUntil)) : 'soon';
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership Paused - The Forge</title>
    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/layout.css">
    <link rel="stylesheet" href="../style/premium-only.css">
</head>

<body>
    <?php $activePage = ''; include '../components/side-menu.php'; ?>

    <main>
        <?php include '../components/flash-messages.php'; ?>

        <div class="premium-gate">
            <div class="premium-gate__icon">&#10052;</div>
            <h1 class="premium-gate__title" style="color: rgba(var(--color-grey-rgb), 0.35);">MEMBERSHIP PAUSED</h1>
            <p class="premium-gate__subtitle">Your subscription is currently frozen.</p>
            <p class="premium-gate__desc">Access to gym features is suspended until <strong><?= htmlspecialchars($untilLabel) ?></strong>. You can resume your membership early at any time from your profile.</p>
            <a href="/src/pages/page_account.php"><button class="btn-page premium-gate__cta">Go to Profile</button></a>
        </div>
    </main>

    <?php include '../components/footer.php'; ?>
</body>
</html>
