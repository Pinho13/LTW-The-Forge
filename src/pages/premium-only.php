<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/page_bootstrap.php');

[$session, $db] = requireAuthenticatedPage();

if ($session->isPremium()) {
    header('Location: /src/pages/classes.php');
    exit;
}

$page = $_GET['page'] ?? 'classes';
$label = match($page) {
    'my-classes' => 'MY CLASSES',
    default      => 'CLASSES',
};
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Feature - The Forge</title>
    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/layout.css">
    <link rel="stylesheet" href="../style/premium-only.css">
    <link rel="stylesheet" href="../style/page-account.css">
</head>

<body>
    <?php $activePage = ''; include '../components/side-menu.php'; ?>

    <main>
        <?php include '../components/flash-messages.php'; ?>

        <div class="premium-gate">
            <div class="premium-gate__icon">&#9733;</div>
            <h1 class="premium-gate__title"><?= htmlspecialchars($label) ?></h1>
            <p class="premium-gate__subtitle">This feature is exclusive to Premium members.</p>
            <p class="premium-gate__desc">Upgrade your plan to unlock unlimited classes, scheduling, and more.</p>
            <button class="btn-page premium-gate__cta" id="upgrade-btn">Be Premium!</button>
        </div>

        <div class="modal-backdrop" id="upgrade-backdrop"></div>

        <dialog id="upgrade-modal" class="auth-modal auth-modal--simple">
            <button class="btn-ghost auth-modal__close" id="upgrade-close">&times;</button>
            <h1 class="auth-modal__title">UPGRADE TO PREMIUM</h1>
            <h2 class="auth-modal__subtitle">39.99€ / month</h2>
            <p class="auth-modal__prompt">You'll get unlimited classes, full facility access, and more. Confirm your upgrade?</p>
            <form method="POST" action="../actions/action_upgrade_plan.php" class="auth-modal__form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($session->generateCsrfToken()) ?>">
                <button type="submit" class="btn-primary" style="margin-top: var(--space-m); margin-bottom: var(--space-s);">CONFIRM UPGRADE</button>
            </form>
            <button class="btn-ghost profile-edit-cancel" id="upgrade-cancel" style="width: 100%; text-align: center; padding: var(--space-xs) 0;">Cancel</button>
        </dialog>
    </main>

    <?php include '../components/footer.php'; ?>

    <script>
    (function () {
        const btn      = document.getElementById('upgrade-btn');
        const modal    = document.getElementById('upgrade-modal');
        const backdrop = document.getElementById('upgrade-backdrop');
        const close    = document.getElementById('upgrade-close');
        const cancel   = document.getElementById('upgrade-cancel');

        function openModal() {
            modal.setAttribute('open', '');
            backdrop.classList.add('modal-backdrop--visible');
        }
        function closeModal() {
            modal.removeAttribute('open');
            backdrop.classList.remove('modal-backdrop--visible');
        }

        btn.addEventListener('click', openModal);
        close.addEventListener('click', closeModal);
        cancel.addEventListener('click', closeModal);
        backdrop.addEventListener('click', closeModal);
    })();
    </script>
</body>
</html>
