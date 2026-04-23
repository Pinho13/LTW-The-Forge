<?php
declare(strict_types=1);

$sessionPath = __DIR__ . '/../../utils/session.php';
$databasePath = __DIR__ . '/../../database/connection.db.php';
$user_classPath = __DIR__ . '/../../database/User.class.php';

require_once($sessionPath);
require_once($databasePath);
require_once($user_classPath);

$session = new Session();
$session->requireLogin('/src/pages/index.php');

$db = getDatabaseConnection();
$user = User::findById($db, $session->getId());

if ($user === null) {
    $session->logout();
    header('Location: /src/pages/index.php');
    exit;
}

$activePage = 'account';
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - The Forge</title>

    <link rel="stylesheet" href="../style/base.css">
    <link rel="stylesheet" href="../style/layout.css">
    <link rel="stylesheet" href="../style/components/logo.css">
    <link rel="stylesheet" href="../style/components/footer.css">
    <link rel="stylesheet" href="../style/components/side-menu.css">
    <link rel="stylesheet" href="../style/components/buttons.css">
    <link rel="stylesheet" href="../style/my-account.css">
</head>
<body>
    <div class="account-page">
        <aside class="account-sidebar">
            <?php include __DIR__ . '/../components/side-menu.php'; ?>
            <?php include __DIR__ . '/../components/sidebar-user-block.php'; ?>
        </aside>

        <main class="account-dashboard">
            <section class="dashboard-header">
                <h1>Welcome back, <?= htmlspecialchars($user->name) ?>!</h1>
                <p>Track your progress, manage your training, and stay on top of your next sessions.</p>
            </section>

            <section class="dashboard-stats">
                <?php
                $value = '12';
                $label = 'Classes Attended';
                include __DIR__ . '/../components/stat-card.php';

                $value = '03';
                $label = 'Active Reservations';
                include __DIR__ . '/../components/stat-card.php';

                $value = '07';
                $label = 'Day Streak';
                include __DIR__ . '/../components/stat-card.php';
                ?>
            </section>

            <?php
            $sectionLabel = 'NEXT CLASS';
            $classTitle = 'HIIT Blast';
            $classMeta = 'Thursday, 23 April · 18:00 · Room B';
            $buttonText = 'VIEW DETAILS';
            $buttonHref = '#';
            include __DIR__ . '/../components/next-class-card.php';
            ?>

            <section class="recent-activity-section">
                <div class="section-heading">
                    <h2>Recent Activity</h2>
                </div>

                <div class="activity-table">
                    <div class="activity-table-head">
                        <span>Activity</span>
                        <span>Date</span>
                        <span>Status</span>
                    </div>

                    <div class="activity-row">
                        <span>Morning Yoga</span>
                        <span>22 Apr 2026</span>
                        <span>Completed</span>
                    </div>

                    <div class="activity-row">
                        <span>Equipment Reservation</span>
                        <span>22 Apr 2026</span>
                        <span>Confirmed</span>
                    </div>

                    <div class="activity-row">
                        <span>HIIT Blast</span>
                        <span>24 Apr 2026</span>
                        <span>Upcoming</span>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>