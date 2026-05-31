<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/page_bootstrap.php');
require_once(__DIR__ . '/../templates/common.tpl.php');
require_once(__DIR__ . '/../../database/models/User.class.php');
require_once(__DIR__ . '/../../database/models/MemberSubscription.class.php');

[$session, $db] = requireAuthenticatedPage();
$user = User::findById($db, $session->getId());
if (!$user) {
    $session->logout();
    header('Location: /src/pages/index.php');
    exit;
}
$planName    = MemberSubscription::getActivePlanName($db, $session->getId()) ?? 'Member';
$isFrozen    = $session->isFrozen();
$frozenUntil = $isFrozen ? MemberSubscription::getFrozenUntil($db, $session->getId()) : null;
$memberSince = date('M Y', strtotime($user->created_at));

$heroInitials = $user->getInitials();

$pfpPath  = __DIR__ . '/../../database/profile_pictures/' . $user->user_id . '.png';
$pfpUrl   = file_exists($pfpPath)
    ? '/database/profile_pictures/' . $user->user_id . '.png?v=' . filemtime($pfpPath)
    : null;
$accountError = $session->popFormError('update_account');
$deleteError  = $session->popFormError('delete_account');
$accountFormData = $session->popFormData('update_account');

$fieldName     = $accountFormData['name']     ?? $user->name;
$fieldUsername = $accountFormData['username'] ?? $user->username;
$fieldEmail    = $accountFormData['email']    ?? $user->email;
$fieldPhone    = $accountFormData['phone']    ?? $user->phone ?? '';
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - The Forge</title>
    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/layout.css">
    <link rel="stylesheet" href="../style/page-account.css">
    <script type="module" src="../scripts/page-account.js"></script>
</head>

<body>
    <?php $activePage = 'profile'; include '../components/side-menu.php'; ?>

    <main>
        <?php include '../components/flash-messages.php'; ?>
        <header>
            <h1>Profile</h1>
        </header>

        <section class="profile-hero">
            <button type="button" class="user-avatar-btn" id="pfp-btn" title="Change profile picture">
                <?php if ($pfpUrl): ?>
                    <img src="<?= htmlspecialchars($pfpUrl) ?>"
                         alt="<?= htmlspecialchars($user->name) ?>"
                         class="user-avatar">
                <?php else: ?>
                    <div class="user-avatar user-avatar--initials" aria-hidden="true">
                        <span><?= htmlspecialchars($heroInitials) ?></span>
                    </div>
                <?php endif; ?>
                <div class="user-avatar__overlay" aria-hidden="true">&#128247;</div>
            </button>

            <div class="profile-hero__info">
                <h2 class="profile-hero__name"><?= htmlspecialchars(strtoupper($user->name)) ?></h2>
                <p class="profile-hero__meta">
                    <span class="profile-hero__badge"><?= htmlspecialchars(strtoupper($planName)) ?></span>
                    <span class="profile-hero__since">· Member since <?= htmlspecialchars($memberSince) ?></span>
                </p>
            </div>
        </section>

        <section class="profile-section">
            <h2 class="profile-section__title">ACCOUNT</h2>
            <form id="account-form" method="post" action="../actions/action_update_account.php" <?= $accountError ? 'data-unlocked' : '' ?>>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($session->getCsrfToken()) ?>">
                <div class="profile-form__grid">
                    <div>
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name"
                               value="<?= htmlspecialchars($fieldName) ?>"
                               autocomplete="name"
                               <?= $accountError ? '' : 'readonly' ?>>
                    </div>
                    <div>
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username"
                               value="<?= htmlspecialchars($fieldUsername) ?>"
                               autocomplete="username"
                               <?= $accountError ? '' : 'readonly' ?>>
                    </div>
                    <div>
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email"
                               value="<?= htmlspecialchars($fieldEmail) ?>"
                               autocomplete="email"
                               <?= $accountError ? '' : 'readonly' ?>>
                    </div>
                    <div>
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone"
                               value="<?= htmlspecialchars($fieldPhone) ?>"
                               placeholder="+351 912 345 678"
                               autocomplete="tel"
                               <?= $accountError ? '' : 'readonly' ?>>
                    </div>
                </div>
            </form>
            <div class="profile-section__actions">
                <?php if ($accountError): ?>
                    <button type="button" id="cancel-btn" class="btn-ghost profile-edit-cancel">Cancel</button>
                    <button type="submit" id="confirm-btn" form="account-form" class="btn-ghost profile-edit-save">Save</button>
                <?php else: ?>
                    <button type="button" id="edit-btn" class="btn-ghost profile-edit-btn">Edit Profile</button>
                <?php endif; ?>
            </div>
        </section>

        <section class="profile-section">
            <h2 class="profile-section__title">Change Password</h2>
            <form method="post" action="../actions/action_change_password.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($session->getCsrfToken()) ?>">
                <div class="profile-form__password-grid">
                    <?php drawPasswordField('current-password', 'current_password', 'Current Password'); ?>
                    <?php drawPasswordField('new-password', 'new_password', 'New Password', 'new-password'); ?>
                    <?php drawPasswordField('confirm-password', 'confirm_password', 'Confirm New', 'new-password'); ?>
                </div>
                <button type="submit" class="btn-ghost profile-edit-save profile-form__submit">Update Password</button>
            </form>
        </section>

        <section class="profile-section profile-section--danger">
            <h2 class="profile-section__title profile-section__title--danger">Danger Zone</h2>

            <div class="danger-zone__item">
                <div class="danger-zone__info">
                    <strong>Log Out</strong>
                    <p>Logs out of account. Goes back to home page.</p>
                </div>
                <button type="button" class="btn-outline" id="logout-btn">Log Out</button>
            </div>

            <div class="danger-zone__item">
                <div class="danger-zone__info">
                    <?php if ($isFrozen): ?>
                        <strong>Membership Paused</strong>
                        <p>Paused until <?= htmlspecialchars(date('M j, Y', strtotime($frozenUntil))) ?>. Resume early at any time.</p>
                    <?php else: ?>
                        <strong>Pause membership</strong>
                        <p>Freeze your subscription for 1, 2, or 3 months.</p>
                    <?php endif; ?>
                </div>
                <?php if ($isFrozen): ?>
                    <form method="post" action="../actions/action_unpause_membership.php">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($session->getCsrfToken()) ?>">
                        <button type="submit" class="btn-outline">Unpause</button>
                    </form>
                <?php else: ?>
                    <button type="button" class="btn-outline" id="pause-btn">Pause</button>
                <?php endif; ?>
            </div>

            <?php if ($session->isMember() && !$isFrozen): ?>
            <div class="danger-zone__item">
                <div class="danger-zone__info">
                    <?php if ($session->isPremium()): ?>
                        <strong>Downgrade to Basic</strong>
                        <p>Switch to the Basic plan. You will lose access to classes and scheduling.</p>
                    <?php else: ?>
                        <strong>Upgrade to Premium</strong>
                        <p>Unlock unlimited classes, full facility access, and more.</p>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn-outline" id="plan-change-btn">
                    <?= $session->isPremium() ? 'Downgrade' : 'Upgrade' ?>
                </button>
            </div>
            <?php endif; ?>

            <div class="danger-zone__item">
                <div class="danger-zone__info">
                    <strong>Delete account</strong>
                    <p>Permanent. We keep nothing.</p>
                </div>
                <button type="button" class="btn-danger" id="delete-btn">Delete Account</button>
            </div>
        </section>
    </main>

    <form id="pfp-form" method="post" enctype="multipart/form-data"
          action="../actions/action_upload_profile_picture.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($session->getCsrfToken()) ?>">
        <input type="file" id="pfp-input" name="photo" accept="image/*" style="display:none">
    </form>

    <div class="modal-backdrop" id="page-backdrop"></div>

    <dialog id="pfp-modal" class="auth-modal auth-modal--simple">
        <button type="button" class="btn-ghost auth-modal__close">&times;</button>
        <h2 class="auth-modal__title">Profile Picture</h2>
        <img id="pfp-preview" src="" alt="Preview" class="pfp-modal__preview">
        <button type="button" id="pfp-confirm-btn" class="btn-primary pfp-modal__confirm">Save Picture</button>
        <p class="auth-modal__switch"><a href="#" id="pfp-cancel-btn">Cancel</a></p>
    </dialog>

    <dialog id="logout-modal" class="auth-modal auth-modal--simple">
        <button type="button" class="btn-ghost auth-modal__close">&times;</button>
        <h2 class="auth-modal__title">Log Out</h2>
        <form method="post" action="../actions/action_logout.php" class="auth-modal__form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($session->getCsrfToken()) ?>">
            <p class="auth-modal__prompt">Are you sure you want to log out?</p>
            <button type="submit" class="btn-primary">Yes, log out</button>
        </form>
        <p class="auth-modal__switch"><a href="#" id="logout-cancel-btn">Cancel</a></p>
    </dialog>

    <dialog id="delete-modal" class="auth-modal" <?= $deleteError ? 'data-open-on-load' : '' ?>>
        <button type="button" class="btn-ghost auth-modal__close">&times;</button>
        <h2 class="auth-modal__title">Delete Account</h2>
        <form method="post" action="../actions/action_delete_account.php" class="auth-modal__form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($session->getCsrfToken()) ?>">
            <p class="auth-modal__prompt">This is permanent and cannot be undone. Enter your password to confirm.</p>
            <label for="delete-password">Password</label>
            <input type="password" id="delete-password" name="password" autocomplete="current-password">
            <button type="submit" class="btn-danger">Yes, delete my account</button>
        </form>
        <p class="auth-modal__switch"><a href="#" id="delete-cancel-btn">Cancel</a></p>
    </dialog>

    <?php if ($session->isMember() && !$isFrozen): ?>
    <dialog id="plan-change-modal" class="auth-modal auth-modal--simple">
        <button type="button" class="btn-ghost auth-modal__close">&times;</button>
        <?php if ($session->isPremium()): ?>
        <h2 class="auth-modal__title">DOWNGRADE TO BASIC</h2>
        <h3 class="auth-modal__subtitle">19.99€ / month</h3>
        <p class="auth-modal__prompt">You will lose access to classes and scheduling. Are you sure?</p>
        <?php else: ?>
        <h2 class="auth-modal__title">UPGRADE TO PREMIUM</h2>
        <h3 class="auth-modal__subtitle">39.99€ / month</h3>
        <p class="auth-modal__prompt">Unlock unlimited classes, full facility access, and more. Confirm your upgrade?</p>
        <?php endif; ?>
        <form method="post" action="../actions/action_change_plan.php" class="auth-modal__form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($session->getCsrfToken()) ?>">
            <button type="submit" class="<?= $session->isPremium() ? 'btn-danger-solid' : 'btn-primary' ?>" style="margin-top: var(--space-m); margin-bottom: var(--space-s);">
                <?= $session->isPremium() ? 'CONFIRM DOWNGRADE' : 'CONFIRM UPGRADE' ?>
            </button>
        </form>
        <button type="button" id="plan-change-cancel" class="btn-ghost profile-edit-cancel" style="width: 100%; text-align: center; padding: var(--space-xs) 0;">Cancel</button>
    </dialog>
    <?php endif; ?>

    <?php if (!$isFrozen): ?>
    <dialog id="pause-modal" class="auth-modal">
        <button type="button" class="btn-ghost auth-modal__close">&times;</button>
        <h2 class="auth-modal__title">Pause Membership</h2>
        <form method="post" action="../actions/action_pause_membership.php" class="auth-modal__form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($session->getCsrfToken()) ?>">
            <input type="hidden" name="duration" id="pause-duration" value="">
            <p class="auth-modal__prompt">Select how long to pause your membership:</p>
            <div class="pause-options">
                <?php foreach (MemberSubscription::ALLOWED_PAUSE_DAYS as $days): ?>
                    <button type="button" class="pause-option" data-days="<?= $days ?>"><?= MemberSubscription::PAUSE_LABELS[$days] ?></button>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="btn-primary" id="pause-confirm-btn" disabled>Confirm Pause</button>
        </form>
    </dialog>
    <?php endif; ?>

    <?php include '../components/footer.php'; ?>

</body>
</html>
