<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/page_bootstrap.php');
require_once(__DIR__ . '/../../database/models/TrainerProfile.class.php');

[$session, $db] = requireAuthenticatedPage();

// If ?id= is given, show single trainer profile
$trainerId = isset($_GET['id']) ? (int) $_GET['id'] : null;

if ($trainerId !== null) {
    $trainer = TrainerProfile::getByUserId($db, $trainerId);
    if (!$trainer) {
        header('Location: /src/pages/trainers.php');
        exit;
    }
    $upcomingClasses = TrainerProfile::getUpcomingClasses($db, $trainerId);

    $pfpPath = __DIR__ . '/../../database/profile_pictures/' . $trainerId . '.png';
    $pfpUrl  = file_exists($pfpPath)
        ? '/database/profile_pictures/' . $trainerId . '.png?v=' . filemtime($pfpPath)
        : null;
    $initials = '';
    foreach (array_slice(array_filter(explode(' ', $trainer['name'])), 0, 2) as $w) {
        $initials .= mb_strtoupper(mb_substr($w, 0, 1));
    }
} else {
    $trainers = TrainerProfile::getAllWithUser($db);
}
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $trainerId ? htmlspecialchars($trainer['name']) . ' - ' : '' ?>Trainers - The Forge</title>
    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/layout.css">
    <link rel="stylesheet" href="../style/trainers.css">
</head>

<body>
    <?php $activePage = 'trainers'; include '../components/side-menu.php'; ?>

    <main>
        <?php include '../components/flash-messages.php'; ?>

        <?php if ($trainerId !== null): ?>

        <div class="trainer-profile-layout">
            <div class="trainer-profile-content">
                <a href="/src/pages/trainers.php" class="back-link">&larr; All Trainers</a>
                <div class="trainer-profile-identity">
                    <?php if ($pfpUrl): ?>
                        <img src="<?= htmlspecialchars($pfpUrl) ?>" alt="<?= htmlspecialchars($trainer['name']) ?>" class="trainer-profile-avatar">
                    <?php else: ?>
                        <div class="trainer-profile-avatar trainer-profile-avatar--initials"><span><?= htmlspecialchars($initials) ?></span></div>
                    <?php endif; ?>
                    <div>
                        <h1><?= htmlspecialchars($trainer['name']) ?></h1>
                        <p class="trainer-hero__handle">@<?= htmlspecialchars($trainer['username']) ?></p>
                    </div>
                </div>

                <?php if ($session->isAdmin()): ?>
                <form method="POST" action="/src/actions/action_toggle_featured.php" class="trainer-feature-form" data-feature-toggle>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($session->getCsrfToken()) ?>">
                    <input type="hidden" name="type" value="trainer">
                    <input type="hidden" name="id" value="<?= (int)$trainer['user_id'] ?>">
                    <input type="hidden" name="return" value="/src/pages/trainers.php?id=<?= (int)$trainer['user_id'] ?>">
                    <button type="submit" class="btn-ghost trainer-feature-btn">
                        <?= $trainer['is_featured'] ? '★ Remove from Homepage' : '☆ Feature on Homepage' ?>
                    </button>
                </form>
                <?php endif; ?>

                <section class="trainer-section">
                    <h2>About</h2>
                    <p><?= htmlspecialchars($trainer['bio'] ?? 'No bio provided.') ?></p>
                </section>

                <?php if (!empty($trainer['specializations'])): ?>
                <section class="trainer-section">
                    <h2>Specializations</h2>
                    <ul class="tag-list">
                        <?php foreach (explode(',', $trainer['specializations']) as $s): ?>
                            <li class="tag"><?= htmlspecialchars(trim($s)) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
                <?php endif; ?>

                <?php if (!empty($trainer['certifications'])): ?>
                <section class="trainer-section">
                    <h2>Certifications</h2>
                    <ul class="tag-list">
                        <?php foreach (explode(',', $trainer['certifications']) as $c): ?>
                            <li class="tag tag--cert"><?= htmlspecialchars(trim($c)) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
                <?php endif; ?>

                <section class="trainer-section">
                    <h2>Upcoming Classes</h2>
                    <?php if (empty($upcomingClasses)): ?>
                        <p class="empty-state">No upcoming classes scheduled.</p>
                    <?php else: ?>
                        <ul class="trainer-class-list">
                            <?php foreach ($upcomingClasses as $cls):
                                $dt    = new DateTimeImmutable($cls['datetime']);
                                $spots = max(0, (int)$cls['capacity'] - (int)$cls['enrolled_count']);
                            ?>
                                <li class="trainer-class-item">
                                    <a href="/src/pages/classes.php#session-<?= (int)$cls['session_id'] ?>" class="trainer-class-item__link">
                                        <div class="trainer-class-item__info">
                                            <strong><?= htmlspecialchars($cls['class_name']) ?></strong>
                                            <?php if ($cls['type_name']): ?>
                                                <span class="tag tag--sm"><?= htmlspecialchars($cls['type_name']) ?></span>
                                            <?php endif; ?>
                                            <p class="trainer-class-item__meta">
                                                <?= htmlspecialchars($dt->format('D j M · H:i')) ?> · <?= htmlspecialchars($cls['room']) ?>
                                            </p>
                                        </div>
                                        <span class="<?= $spots === 0 ? 'status status--missed' : 'status status--enrolled' ?>">
                                            <?= $spots === 0 ? 'Full' : $spots . ' spots left' ?>
                                        </span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </section>
            </div>
        </div>

        <?php else: ?>

        <header>
            <h1>Trainers</h1>
        </header>

        <?php if (empty($trainers)): ?>
            <p class="empty-state">No trainers available at the moment.</p>
        <?php else: ?>
            <ul class="trainer-list">
                <?php foreach ($trainers as $i => $t):
                    $pfpPath = __DIR__ . '/../../database/profile_pictures/' . $t['user_id'] . '.png';
                    $pfpUrl  = file_exists($pfpPath)
                        ? '/database/profile_pictures/' . $t['user_id'] . '.png?v=' . filemtime($pfpPath)
                        : null;
                    $initials = '';
                    foreach (array_slice(array_filter(explode(' ', $t['name'])), 0, 2) as $w) {
                        $initials .= mb_strtoupper(mb_substr($w, 0, 1));
                    }
                    $reversed = ($i % 2 === 1) ? ' trainer-card--reversed' : '';
                ?>
                <li class="trainer-card<?= $reversed ?>">
                    <a href="?id=<?= (int)$t['user_id'] ?>" class="trainer-card__link">
                        <div class="trainer-card__avatar-wrap">
                            <?php if ($pfpUrl): ?>
                                <img src="<?= htmlspecialchars($pfpUrl) ?>" alt="<?= htmlspecialchars($t['name']) ?>" class="trainer-card__avatar">
                            <?php else: ?>
                                <div class="trainer-card__avatar trainer-card__avatar--initials"><span><?= htmlspecialchars($initials) ?></span></div>
                            <?php endif; ?>
                        </div>
                        <div class="trainer-card__content">
                            <div class="trainer-card__body">
                                <h2 class="trainer-card__name"><?= htmlspecialchars($t['name']) ?></h2>
                                <?php if (!empty($t['specializations'])): ?>
                                    <p class="trainer-card__spec"><?= htmlspecialchars($t['specializations']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($t['bio'])): ?>
                                    <p class="trainer-card__bio"><?= htmlspecialchars(mb_strimwidth($t['bio'], 0, 140, '…')) ?></p>
                                <?php endif; ?>
                            </div>
                            <span class="trainer-card__cta">View Profile &rarr;</span>
                        </div>
                    </a>
                    <?php if ($session->isAdmin()): ?>
                    <form method="POST" action="/src/actions/action_toggle_featured.php" class="trainer-card__feature-form" data-feature-toggle>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($session->getCsrfToken()) ?>">
                        <input type="hidden" name="type" value="trainer">
                        <input type="hidden" name="id" value="<?= (int)$t['user_id'] ?>">
                        <input type="hidden" name="return" value="/src/pages/trainers.php">
                        <button type="submit" class="trainer-card__feature-btn <?= $t['is_featured'] ? 'trainer-card__feature-btn--active' : '' ?>">
                            <?= $t['is_featured'] ? '★ Featured' : '☆ Feature' ?>
                        </button>
                    </form>
                    <?php endif; ?>
                </li>

                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php endif; ?>
    </main>

    <?php include '../components/footer.php'; ?>
    <script type="module">
        import { initFeatureSwap } from '../scripts/feature-swap.js';
        initFeatureSwap();
    </script>
</body>
</html>
