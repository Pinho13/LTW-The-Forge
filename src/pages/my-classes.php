<?php
declare(strict_types=1);
require_once(__DIR__ . '/../../utils/page_bootstrap.php');
require_once(__DIR__ . '/../../database/Enrollment.class.php');
require_once(__DIR__ . '/../templates/enrollment.tpl.php');

[$session, $db] = requireAuthenticatedPage();
$memberId = $session->getId();

$tab = $_GET['tab'] ?? 'upcoming';
if (!in_array($tab, ['upcoming', 'past'], true)) $tab = 'upcoming';

$upcomingRaw = Enrollment::getUpcomingForMember($db, $memberId, 0);
$upcomingHasMore = count($upcomingRaw) > 30;
$upcoming = array_slice($upcomingRaw, 0, 30);

$pastRaw = Enrollment::getPastForMember($db, $memberId, 0);
$pastHasMore = count($pastRaw) > 30;
$past = array_slice($pastRaw, 0, 30);

$list = $tab === 'upcoming' ? $upcoming : $past;
$hasMore = $tab === 'upcoming' ? $upcomingHasMore : $pastHasMore;
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

                <div id="load-more-container" style="<?= !$hasMore ? 'display:none' : '' ?>">
                    <button type="button" id="load-more-btn" class="btn-ghost" data-offset="30" data-tab="<?= $tab ?>">
                        Load more
                    </button>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php include '../components/footer.php'; ?>

    <div class="modal-backdrop" id="page-backdrop"></div>

    <dialog id="cancel-modal" class="auth-modal">
        <button type="button" class="btn-ghost auth-modal__close">&times;</button>
        <h2 class="auth-modal__title">Cancel Class</h2>
        <form method="POST" action="../actions/action_cancel_enrollment.php" class="auth-modal__form">
            <input type="hidden" name="enrollment_id" id="cancel-enrollment-id">
            <p class="auth-modal__prompt">Cancel your spot in <strong id="cancel-class-name"></strong>?</p>
            <button type="submit" class="btn-danger">Yes, cancel</button>
        </form>
        <p class="auth-modal__switch"><a href="#" id="cancel-keep-btn">Keep my spot</a></p>
    </dialog>

    <script src="../scripts/my-classes.js"></script>

</body>
</html>
