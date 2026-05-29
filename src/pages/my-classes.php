<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/page_bootstrap.php');
require_once(__DIR__ . '/../../database/models/Enrollment.class.php');
require_once(__DIR__ . '/../templates/enrollment.tpl.php');

[$session, $db] = requireAuthenticatedPage();

if ($session->isMember() && !$session->isPremium()) {
    header('Location: /src/pages/my-account.php');
    exit;
}

$memberId = $session->getId();

$tab = $_GET['tab'] ?? 'upcoming';
if (!in_array($tab, ['upcoming', 'past'], true)) $tab = 'upcoming';

$upcomingRaw = Enrollment::getUpcomingForMember($db, $memberId, 0);
$upcomingHasMore = count($upcomingRaw) > Enrollment::PAGE_SIZE;
$upcoming = array_slice($upcomingRaw, 0, Enrollment::PAGE_SIZE);

$pastRaw = Enrollment::getPastForMember($db, $memberId, 0);
$pastHasMore = count($pastRaw) > Enrollment::PAGE_SIZE;
$past = array_slice($pastRaw, 0, Enrollment::PAGE_SIZE);

$list = $tab === 'upcoming' ? $upcoming : $past;
$hasMore = $tab === 'upcoming' ? $upcomingHasMore : $pastHasMore;

$now = date('Y-m-d H:i:s');
$staleEnrollments = Enrollment::getStaleForMember($db, $memberId, $now);
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Classes - The Forge</title>
    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/layout.css">
    <link rel="stylesheet" href="../style/my-classes.css">
</head>
<body>
    <?php $activePage = 'my-classes'; include '../components/side-menu.php'; ?>

    <main>
        <?php include '../components/flash-messages.php'; ?>
        <header>
            <h1>My Classes</h1>
        </header>

        <section>
            <div class="tabs">
                <a href="?tab=upcoming" class="tab <?= $tab === 'upcoming' ? 'tab--active' : '' ?>">
                    Upcoming · <?= count($upcoming) . ($upcomingHasMore ? '+' : '') ?>
                </a>
                <a href="?tab=past" class="tab <?= $tab === 'past' ? 'tab--active' : '' ?>">
                    Past · <?= count($past) . ($pastHasMore ? '+' : '') ?>
                </a>
            </div>

            <?php if (!empty($list)): ?>
            <div class="enrollment-sort" id="enrollment-sort">
                <span class="enrollment-sort__label">Sort by:</span>
                <button type="button" class="enrollment-sort__btn enrollment-sort__btn--active" data-sort="date">Date</button>
                <button type="button" class="enrollment-sort__btn" data-sort="type">Type</button>
                <button type="button" class="enrollment-sort__btn" data-sort="intensity">Intensity</button>
            </div>
            <?php endif; ?>

            <?php if (empty($list)): ?>
                <p class="empty">
                    <?= $tab === 'upcoming'
                        ? 'No upcoming classes. Browse the schedule and reserve a spot.'
                        : 'No past classes yet.' ?>
                </p>
            <?php else: ?>
                <ul class="enrollment-list" id="enrollment-list">
                    <?php foreach ($list as $e): drawEnrollmentItem($e, $tab); endforeach; ?>
                </ul>

                <?php if ($hasMore): ?>
                <div id="load-more-container">
                    <button type="button" id="load-more-btn" class="btn-ghost" data-offset="<?= Enrollment::PAGE_SIZE ?>" data-tab="<?= $tab ?>">
                        Load more
                    </button>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </main>

    <?php if (!empty($staleEnrollments)): ?>
    <div class="stale-banner" id="stale-banner">
        <p class="stale-banner__text">
            <?= count($staleEnrollments) === 1
                ? '1 class is awaiting a status update.'
                : count($staleEnrollments) . ' classes are awaiting a status update.' ?>
        </p>
        <div class="stale-banner__actions">
            <button type="button" class="btn-outline btn-sm" id="stale-open-btn">Update now</button>
            <button type="button" class="btn-ghost" id="stale-dismiss-btn">&times;</button>
        </div>
    </div>
    <?php endif; ?>

    <?php include '../components/footer.php'; ?>

    <div class="modal-backdrop" id="page-backdrop"></div>

    <dialog id="cancel-modal" class="auth-modal">
        <button type="button" class="btn-ghost auth-modal__close">&times;</button>
        <h2 class="auth-modal__title">Cancel Class</h2>
        <form method="POST" action="../actions/action_cancel_enrollment.php" class="auth-modal__form">
            <input type="hidden" name="csrf_token" value="<?= $session->getCsrfToken() ?>">
            <input type="hidden" name="enrollment_id" id="cancel-enrollment-id">
            <p class="auth-modal__prompt">Cancel your spot in <span id="cancel-class-name"></span>?</p>
            <button type="submit" class="btn-danger modal-action-btn">Yes, cancel</button>
        </form>
        <p class="auth-modal__switch"><a href="#" id="cancel-keep-btn">Keep my spot</a></p>
    </dialog>

    <dialog id="review-modal" class="auth-modal">
        <button type="button" class="btn-ghost auth-modal__close">&times;</button>
        <h2 class="auth-modal__title" id="review-modal-title">Leave a Review</h2>
        <form method="POST" action="../actions/action_submit_review.php" class="auth-modal__form" id="review-form">
            <input type="hidden" name="csrf_token" value="<?= $session->getCsrfToken() ?>">
            <input type="hidden" name="class_id" id="review-class-id">
            <p class="auth-modal__prompt">Rate <span id="review-class-name"></span> with <span id="review-trainer-name"></span></p>
            <div class="star-rating" id="star-rating" role="group" aria-label="Rating">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <button type="button" class="star" data-value="<?= $i ?>" aria-label="<?= $i ?> star<?= $i > 1 ? 's' : '' ?>">&#9733;</button>
                <?php endfor; ?>
            </div>
            <input type="hidden" name="rating" id="review-rating" value="0">
            <label for="review-comment">Comment <span class="review-optional">(optional)</span></label>
            <textarea id="review-comment" name="comment" rows="3" placeholder="Share your experience…" maxlength="500"></textarea>
            <button type="submit" class="btn-primary modal-action-btn" id="review-submit-btn">Submit review</button>
        </form>
    </dialog>

    <dialog id="stale-modal" class="auth-modal">
        <button type="button" class="btn-ghost auth-modal__close">&times;</button>
        <h2 class="auth-modal__title">Update Status</h2>
        <p class="stale-progress" id="stale-progress"></p>
        <p class="auth-modal__prompt"><span id="stale-class-name"></span> with <span id="stale-trainer-name"></span></p>
        <p class="stale-date" id="stale-class-date"></p>
        <div class="stale-actions">
            <button type="button" class="btn-danger btn-sm" id="stale-missed-btn">Missed</button>
            <button type="button" class="btn-primary btn-sm" id="stale-completed-btn">Completed</button>
        </div>
        <p class="stale-error" id="stale-error"></p>
        <p class="auth-modal__switch"><a href="#" id="stale-later-btn">Later</a></p>
    </dialog>

    <script>
        const STALE_ENROLLMENTS = <?= json_encode($staleEnrollments) ?>;
        const CSRF_TOKEN = <?= json_encode($session->getCsrfToken()) ?>;
    </script>
    <script src="../scripts/my-classes.js"></script>

</body>
</html>
