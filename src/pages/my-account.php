<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/page_bootstrap.php');
require_once(__DIR__ . '/../../database/models/Enrollment.class.php');
require_once(__DIR__ . '/../../database/models/GymVisit.class.php');

[$session, $db] = requireAuthenticatedPage();
$classesThisMonth     = Enrollment::countEnrolledThisMonth($db, $session->getId());
$upcomingReservations = Enrollment::countUpcoming($db, $session->getId());
$weeklyStreak         = GymVisit::getWeeklyStreak($db, $session->getId());
$nextClass            = Enrollment::findNextForMember($db, $session->getId());
$recentActivity       = Enrollment::getRecentActivity($db, $session->getId());
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - The Forge</title>
    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/layout.css">
    <link rel="stylesheet" href="../style/my-account.css">
</head>

<body>
    <?php $activePage = 'account'; include '../components/side-menu.php'; ?>

    <main>
        <?php include '../components/flash-messages.php'; ?>
        <header>
            <h1>Welcome back, <?= htmlspecialchars($session->getName()) ?>!</h1>
        </header>

        <section class="stats">
            <article class="stat-card">
                <h2 class="stat-card__value"><?= $classesThisMonth ?></h2>
                <p class="stat-card__label">Classes This Month</p>
            </article>

            <article class="stat-card">
                <h2 class="stat-card__value"><?= $upcomingReservations ?></h2>
                <p class="stat-card__label">Upcoming Reservations</p>
            </article>

            <article class="stat-card">
                <h2 class="stat-card__value">Weekly Streak</h2>
                <ul class="streak">
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <li class="streak__item <?= $i <= $weeklyStreak ? 'streak__item--active' : '' ?>">
                            <img src="../assets/icons/streak.svg" alt="Fire icon">
                        </li>
                    <?php endfor; ?>
                </ul>
            </article>
        </section>

        <section class="class-preview">
            <h2 class="class-preview__title">NEXT CLASS</h2>
            <?php if ($nextClass): ?>
                <ul class="class-preview__details">
                    <li><?= htmlspecialchars($nextClass['class_name']) ?></li>
                    <li><?= htmlspecialchars(date('l H:i', strtotime($nextClass['datetime']))) ?></li>
                    <li><?= htmlspecialchars($nextClass['room']) ?></li>
                    <?php if ($nextClass['trainer_name']): ?>
                        <li>Trainer: <?= htmlspecialchars($nextClass['trainer_name']) ?></li>
                    <?php endif; ?>
                </ul>
            <?php else: ?>
                <p class="class-preview__empty">Checkout our classes tab! We are sure you will love one of them!</p>
            <?php endif; ?>
        </section>

        <section class="activity">
            <h2>RECENT ACTIVITY</h2>

            <?php if (empty($recentActivity)): ?>
                <p class="activity-list__empty">You have no recent activity! Come try out one of our classes!</p>
            <?php else: ?>
                <ul class="activity-list">
                    <?php foreach ($recentActivity as $activity): ?>
                        <li class="activity-list__item">
                            <span class="activity-list__name"><?= htmlspecialchars($activity['class_name']) ?></span>
                            <span class="activity-list__date"><?= htmlspecialchars(date('D j M', strtotime($activity['datetime']))) ?></span>
                            <span class="activity-list__status status status--<?= $activity['status'] ?>"><?= ucfirst($activity['status']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </main>

    <?php include '../components/footer.php'; ?>

</body>
</html>
