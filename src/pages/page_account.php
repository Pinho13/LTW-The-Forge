<?php
declare(strict_types=1);
require_once(__DIR__ . '/../../utils/page_bootstrap.php');
require_once(__DIR__ . '/../templates/common.tpl.php');
require_once(__DIR__ . '/../../database/User.class.php');
require_once(__DIR__ . '/../../database/MemberSubscription.class.php');

[$session, $db] = requireAuthenticatedPage();
$user        = User::findById($db, $session->getId());
$planName    = MemberSubscription::getActivePlanName($db, $session->getId()) ?? 'Member';
$memberSince = date('M Y', strtotime($user->created_at));

$heroInitials = $user->getInitials();

$pfpPath  = __DIR__ . '/../../database/profile_pictures/' . $user->user_id . '.png';
$pfpUrl   = file_exists($pfpPath)
    ? '/database/profile_pictures/' . $user->user_id . '.png?v=' . filemtime($pfpPath)
    : null;
$uploadError = $session->popFormError('upload_pfp');

$accountError    = $session->popFormError('update_account');
$accountSuccess  = $session->popFormSuccess('update_account');
$passwordError   = $session->popFormError('change_password');
$passwordSuccess = $session->popFormSuccess('change_password');
$deleteError     = $session->popFormError('delete_account');
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
                <button type="button" id="unlock-btn" class="btn-secondary">
                    <?= $accountError ? 'Discard Changes' : 'Unlock Info' ?>
                </button>
                <button type="submit" id="confirm-btn" form="account-form" class="btn-primary" <?= $accountError ? '' : 'disabled' ?>>
                    Confirm Changes
                </button>
            </div>
            <?php if ($accountError): ?>
                <p class="profile-form__feedback profile-form__error"><?= htmlspecialchars($accountError) ?></p>
            <?php elseif ($accountSuccess): ?>
                <p class="profile-form__feedback profile-form__success"><?= htmlspecialchars($accountSuccess) ?></p>
            <?php endif; ?>
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
                <button type="submit" class="btn-primary profile-form__submit">Update Password</button>
                <?php if ($passwordError): ?>
                    <p class="profile-form__feedback profile-form__error"><?= htmlspecialchars($passwordError) ?></p>
                <?php elseif ($passwordSuccess): ?>
                    <p class="profile-form__feedback profile-form__success"><?= htmlspecialchars($passwordSuccess) ?></p>
                <?php endif; ?>
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
                    <strong>Pause membership</strong>
                    <p>Freeze your subscription for up to 60 days.</p>
                </div>
                <button type="button" class="btn-outline" id="pause-btn">Pause</button>
            </div>

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

    <dialog id="pfp-modal" class="auth-modal" <?= $uploadError ? 'data-open-on-load' : '' ?>>
        <button type="button" class="btn-ghost auth-modal__close">&times;</button>
        <h2 class="auth-modal__title">Profile Picture</h2>
        <img id="pfp-preview" src="" alt="Preview" class="pfp-modal__preview">
        <?php if ($uploadError): ?>
            <p class="auth-modal__error"><?= htmlspecialchars($uploadError) ?></p>
        <?php endif; ?>
        <button type="button" id="pfp-confirm-btn" class="btn-primary pfp-modal__confirm">Save Picture</button>
        <p class="auth-modal__switch"><a href="#" id="pfp-cancel-btn">Cancel</a></p>
    </dialog>

    <dialog id="logout-modal" class="auth-modal">
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
            <?php if ($deleteError): ?>
                <p class="auth-modal__error"><?= htmlspecialchars($deleteError) ?></p>
            <?php endif; ?>
            <button type="submit" class="btn-danger">Yes, delete my account</button>
        </form>
        <p class="auth-modal__switch"><a href="#" id="delete-cancel-btn">Cancel</a></p>
    </dialog>

    <dialog id="pause-modal" class="auth-modal">
        <button type="button" class="btn-ghost auth-modal__close">&times;</button>
        <h2 class="auth-modal__title">Pause Membership</h2>
        <form method="post" action="../actions/action_pause_membership.php" class="auth-modal__form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($session->getCsrfToken()) ?>">
            <input type="hidden" name="duration" id="pause-duration" value="">
            <p class="auth-modal__prompt">Select how long to freeze your subscription:</p>
            <div class="pause-options">
                <?php foreach (MemberSubscription::ALLOWED_PAUSE_DAYS as $days): ?>
                    <button type="button" class="pause-option" data-days="<?= $days ?>"><?= $days ?> Days</button>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="btn-primary" id="pause-confirm-btn" disabled>Confirm Pause</button>
        </form>
    </dialog>

    <?php include '../components/footer.php'; ?>

</body>
</html>
