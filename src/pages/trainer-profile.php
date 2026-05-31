<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/page_bootstrap.php');
require_once(__DIR__ . '/../../database/models/TrainerProfile.class.php');

[$session, $db] = requireAuthenticatedPage();

if (!$session->isTrainer() && !$session->isAdmin()) {
    header('Location: /src/pages/my-account.php');
    exit;
}

$userId  = $session->getId();
$profile = TrainerProfile::getByUserId($db, $userId);

$pfpPath = __DIR__ . '/../../database/profile_pictures/' . $userId . '.png';
$pfpUrl  = file_exists($pfpPath)
    ? '/database/profile_pictures/' . $userId . '.png?v=' . filemtime($pfpPath)
    : null;

$initials = '';
$name = $profile['name'] ?? '';
foreach (array_slice(array_filter(explode(' ', $name)), 0, 2) as $w) {
    $initials .= mb_strtoupper(mb_substr($w, 0, 1));
}
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - The Forge</title>
    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/layout.css">
    <link rel="stylesheet" href="../style/trainers.css">
    <link rel="stylesheet" href="../style/trainer-profile.css">
</head>
<body>
    <?php $activePage = 'trainer-profile'; include '../components/side-menu.php'; ?>

    <main>
        <?php include '../components/flash-messages.php'; ?>

        <header>
            <h1>My Profile</h1>
        </header>

        <div class="trainer-edit-layout">

            <!-- Left: photo + form -->
            <div class="trainer-edit-left">
                <div class="trainer-edit-avatar-wrap">
                    <button type="button" class="user-avatar-btn trainer-edit-avatar-btn" id="pfp-btn" title="Change profile picture">
                        <?php if ($pfpUrl): ?>
                            <img src="<?= htmlspecialchars($pfpUrl) ?>" alt="<?= htmlspecialchars($name) ?>" class="user-avatar trainer-edit-avatar-img" id="edit-avatar-img">
                        <?php else: ?>
                            <div class="user-avatar user-avatar--initials trainer-edit-avatar-img" aria-hidden="true">
                                <span><?= htmlspecialchars($initials) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="user-avatar__overlay" aria-hidden="true">&#128247;</div>
                    </button>
                    <p class="trainer-edit-avatar-hint">Click to change photo</p>
                </div>

                <form method="POST" action="/src/actions/action_update_trainer_profile.php" class="trainer-edit-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($session->getCsrfToken()) ?>">

                    <label for="tp-bio">Bio</label>
                    <textarea id="tp-bio" name="bio" rows="4" maxlength="1000" placeholder="Tell members about yourself…"><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>

                    <label for="tp-spec">Specializations <span class="field-hint">comma-separated</span></label>
                    <input type="text" id="tp-spec" name="specializations" maxlength="500"
                           value="<?= htmlspecialchars($profile['specializations'] ?? '') ?>"
                           placeholder="e.g. HIIT, Yoga, Cardio">

                    <label for="tp-cert">Certifications <span class="field-hint">comma-separated</span></label>
                    <input type="text" id="tp-cert" name="certifications" maxlength="500"
                           value="<?= htmlspecialchars($profile['certifications'] ?? '') ?>"
                           placeholder="e.g. ACE Personal Trainer, RYT-200">

                    <button type="submit" class="btn-primary">Save Profile</button>
                </form>
            </div>

            <!-- Right: public preview -->
            <div class="trainer-edit-right">
                <h2 class="trainer-preview-heading">Public Profile Preview</h2>
                <div class="trainer-preview-box">
                    <div class="trainer-profile-identity">
                        <?php if ($pfpUrl): ?>
                            <img src="<?= htmlspecialchars($pfpUrl) ?>" alt="" class="trainer-profile-avatar" id="preview-avatar-img">
                        <?php else: ?>
                            <div class="trainer-profile-avatar trainer-profile-avatar--initials" id="preview-avatar-initials"><span><?= htmlspecialchars($initials) ?></span></div>
                        <?php endif; ?>
                        <div>
                            <h1><?= htmlspecialchars($profile['name'] ?? '') ?></h1>
                            <p class="trainer-hero__handle">@<?= htmlspecialchars($profile['username'] ?? '') ?></p>
                        </div>
                    </div>

                    <section class="trainer-section">
                        <h2>About</h2>
                        <p id="preview-bio"><?= htmlspecialchars($profile['bio'] ?? 'No bio provided.') ?></p>
                    </section>

                    <section class="trainer-section" id="preview-spec-section" <?= empty(trim($profile['specializations'] ?? '')) ? 'hidden' : '' ?>>
                        <h2>Specializations</h2>
                        <ul class="tag-list" id="preview-spec-list">
                            <?php foreach (array_filter(array_map('trim', explode(',', $profile['specializations'] ?? ''))) as $s): ?>
                                <li class="tag"><?= htmlspecialchars($s) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </section>

                    <section class="trainer-section" id="preview-cert-section" <?= empty(trim($profile['certifications'] ?? '')) ? 'hidden' : '' ?>>
                        <h2>Certifications</h2>
                        <ul class="tag-list" id="preview-cert-list">
                            <?php foreach (array_filter(array_map('trim', explode(',', $profile['certifications'] ?? ''))) as $c): ?>
                                <li class="tag tag--cert"><?= htmlspecialchars($c) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                </div>
            </div>

        </div>

    </main>

    <?php include '../components/footer.php'; ?>

    <!-- Hidden file input + preview modal (same pattern as my-account) -->
    <form id="pfp-form" method="post" enctype="multipart/form-data"
          action="../actions/action_upload_profile_picture.php?return=trainer-profile">
        <input type="file" id="pfp-input" name="photo" accept="image/*" style="display:none">
    </form>

    <div class="modal-backdrop" id="page-backdrop"></div>

    <dialog id="pfp-modal" class="auth-modal auth-modal--simple">
        <button type="button" class="btn-ghost auth-modal__close" id="pfp-close">&times;</button>
        <h2 class="auth-modal__title">Preview</h2>
        <img id="pfp-preview" src="" alt="Preview" class="pfp-modal__preview">
        <button type="button" id="pfp-confirm-btn" class="btn-primary pfp-modal__confirm">Save Picture</button>
        <button type="button" id="pfp-cancel-btn" class="btn-ghost pfp-modal__cancel">Cancel</button>
    </dialog>

    <script src="../scripts/trainer-profile.js"></script>
</body>
</html>
