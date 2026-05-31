<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/page_bootstrap.php');
require_once(__DIR__ . '/../../database/models/TrainerAnalytics.class.php');

[$session, $db] = requireAuthenticatedPage();

if (!$session->isTrainer() && !$session->isAdmin()) {
    header('Location: /src/pages/my-account.php');
    exit;
}

$trainerId      = $session->getId();
$stats          = TrainerAnalytics::getStats($db, $trainerId);
$enrollTrend    = TrainerAnalytics::getEnrollmentTrend($db, $trainerId, 6);
$classesByEnrol = TrainerAnalytics::getClassesByEnrollment($db, $trainerId, 8);
$sessionsByDay  = TrainerAnalytics::getSessionsByDayOfWeek($db, $trainerId);
$upcoming       = TrainerAnalytics::getUpcomingSessions($db, $trainerId, 5);
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - The Forge</title>
    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/layout.css">
    <link rel="stylesheet" href="../style/admin-dashboard.css">
    <link rel="stylesheet" href="../style/admin-analytics.css">
</head>
<body>
    <?php $activePage = 'trainer-dashboard'; include '../components/side-menu.php'; ?>

    <main>
        <?php include '../components/flash-messages.php'; ?>

        <header>
            <h1>Dashboard</h1>
        </header>

        <!-- ── Stat cards ── -->
        <section class="dash-stats">

            <div class="dash-stat dash-stat--link" role="button" tabindex="0" id="upcoming-stat">
                <p class="dash-stat__label">Upcoming Sessions</p>
                <p class="dash-stat__value"><?= $stats['upcomingSessions'] ?></p>
                <p class="dash-stat__sub"><?= $stats['sessionsThisWeek'] ?> this week</p>
            </div>

            <div class="dash-stat">
                <p class="dash-stat__label">Total Students</p>
                <p class="dash-stat__value"><?= $stats['totalStudents'] ?></p>
                <p class="dash-stat__sub">unique members taught</p>
            </div>

            <div class="dash-stat">
                <p class="dash-stat__label">Your Classes</p>
                <p class="dash-stat__value"><?= $stats['totalClasses'] ?></p>
                <p class="dash-stat__sub">on the schedule</p>
            </div>

            <div class="dash-stat">
                <p class="dash-stat__label">Avg Rating</p>
                <p class="dash-stat__value">
                    <?php if ($stats['avgRating']): ?>
                        <?= number_format((float)$stats['avgRating'], 1) ?><span class="dash-stat__denom"> / 5</span>
                    <?php else: ?>
                        <span style="font-size:var(--font-size-m);color:var(--color-grey)">—</span>
                    <?php endif; ?>
                </p>
                <p class="dash-stat__sub">across all classes</p>
            </div>

        </section>

        <!-- ── Row 1: Enrollment trend + Sessions by day ── -->
        <div class="analytics-row analytics-row--2col">

            <div class="analytics-panel">
                <div class="analytics-panel__header">
                    <h2 class="analytics-panel__title">Enrollment Trend</h2>
                    <span class="analytics-panel__sub">Your classes, last 6 months</span>
                </div>
                <div class="analytics-panel__body">
                    <canvas id="chart-enrollment" height="220"></canvas>
                </div>
            </div>

            <div class="analytics-panel">
                <div class="analytics-panel__header">
                    <h2 class="analytics-panel__title">Sessions by Day</h2>
                    <span class="analytics-panel__sub">All time</span>
                </div>
                <div class="analytics-panel__body">
                    <canvas id="chart-days" height="220"></canvas>
                </div>
            </div>

        </div>

        <!-- ── Row 2: Classes by enrollment ── -->
        <div class="analytics-row analytics-row--full">

            <div class="analytics-panel">
                <div class="analytics-panel__header">
                    <h2 class="analytics-panel__title">Your Classes</h2>
                    <span class="analytics-panel__sub">By total enrollments</span>
                </div>
                <div class="analytics-panel__body">
                    <canvas id="chart-classes" height="200"></canvas>
                </div>
            </div>

        </div>

        <!-- ── Row 3: Upcoming sessions table ── -->
        <div class="analytics-row analytics-row--full" id="upcoming-panel">

            <div class="analytics-panel">
                <div class="analytics-panel__header">
                    <h2 class="analytics-panel__title">Upcoming Sessions</h2>
                    <span class="analytics-panel__sub">Next <?= count($upcoming) ?> scheduled</span>
                </div>
                <div class="analytics-panel__body analytics-panel__body--table">
                    <?php if (empty($upcoming)): ?>
                    <p style="color:var(--color-grey);font-size:var(--font-size-s);padding:var(--space-m) 0">No upcoming sessions scheduled.</p>
                    <?php else: ?>
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th>Date &amp; Time</th>
                                <th>Room</th>
                                <th class="col-num">Enrolled</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($upcoming as $s):
                            $dt = new DateTimeImmutable($s['datetime']);
                            $today = date('Y-m-d');
                            $tomorrow = date('Y-m-d', strtotime('+1 day'));
                            $d = $dt->format('Y-m-d');
                            $label = $d === $today ? 'Today ' : ($d === $tomorrow ? 'Tomorrow ' : $dt->format('D M j '));
                            $label .= $dt->format('H:i');
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($s['name']) ?></td>
                            <td style="color:var(--color-grey);white-space:nowrap"><?= htmlspecialchars($label) ?></td>
                            <td style="color:var(--color-grey)"><?= htmlspecialchars($s['room'] ?? '—') ?></td>
                            <td class="col-num">
                                <?= (int)$s['enrolled'] ?>
                                <?php if ($s['capacity']): ?>
                                    <span style="color:var(--color-grey);font-size:var(--font-size-xxs)">/ <?= (int)$s['capacity'] ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </main>

    <?php include '../components/footer.php'; ?>

    <style>
        .dash-stat--link { cursor: pointer; transition: border-color 0.15s, background 0.15s; }
        .dash-stat--link:hover { border-color: var(--color-gold); background: rgba(var(--color-gold-rgb), 0.06); }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    (function () {
        const GOLD        = '#A8843C';
        const LIGHT_GOLD  = '#D4A853';
        const GREY        = '#ACABA2';
        const ALMOST_WHITE= '#F2EDE4';
        const GRID_COLOR  = 'rgba(172,171,162,0.12)';

        const PALETTE = ['#A8843C','#C8C7BF','#8C8C7A','#5A5A52','#D4A853','#3A3A34','#B0AFA6','#6E6E64'];

        Chart.defaults.color = GREY;
        Chart.defaults.font.family = '"Instrument Sans", sans-serif';
        Chart.defaults.font.size = 11;

        const baseAxes = {
            x: { grid: { color: GRID_COLOR }, ticks: { color: GREY } },
            y: { grid: { color: GRID_COLOR }, ticks: { color: GREY }, beginAtZero: true }
        };

        /* ── 1. Enrollment Trend (line) ── */
        const enrollData = <?= json_encode(array_values($enrollTrend)) ?>;
        new Chart(document.getElementById('chart-enrollment'), {
            type: 'line',
            data: {
                labels: enrollData.map(r => r.month),
                datasets: [{
                    label: 'Enrollments',
                    data: enrollData.map(r => parseInt(r.enrollments)),
                    borderColor: GOLD,
                    backgroundColor: 'rgba(168,132,60,0.12)',
                    borderWidth: 2,
                    pointBackgroundColor: GOLD,
                    pointRadius: 4,
                    fill: true,
                    tension: 0.35
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: baseAxes
            }
        });

        /* ── 2. Sessions by day of week (bar) ── */
        const dayData = <?= json_encode(array_values($sessionsByDay)) ?>;
        const dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
        new Chart(document.getElementById('chart-days'), {
            type: 'bar',
            data: {
                labels: dayNames,
                datasets: [{
                    label: 'Sessions',
                    data: dayData,
                    backgroundColor: dayData.map(v => {
                        const max = Math.max(...dayData);
                        const ratio = max > 0 ? v / max : 0;
                        return `rgba(168,132,60,${0.2 + ratio * 0.75})`;
                    }),
                    borderColor: GOLD,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: ALMOST_WHITE } },
                    y: { grid: { color: GRID_COLOR }, ticks: { color: GREY }, beginAtZero: true }
                }
            }
        });

        /* ── 3. Classes by enrollment (horizontal bar) ── */
        const classData = <?= json_encode(array_values($classesByEnrol)) ?>;
        new Chart(document.getElementById('chart-classes'), {
            type: 'bar',
            data: {
                labels: classData.map(r => r.name),
                datasets: [{
                    label: 'Total Enrollments',
                    data: classData.map(r => parseInt(r.total_enrolled) || 0),
                    backgroundColor: classData.map((_, i) => PALETTE[i % PALETTE.length] + 'cc'),
                    borderColor:     classData.map((_, i) => PALETTE[i % PALETTE.length]),
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: GRID_COLOR }, ticks: { color: GREY }, beginAtZero: true },
                    y: { grid: { display: false }, ticks: { color: ALMOST_WHITE } }
                }
            }
        });
    })();

    document.getElementById('upcoming-stat').addEventListener('click', () => {
        document.getElementById('upcoming-panel').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
    document.getElementById('upcoming-stat').addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            document.getElementById('upcoming-panel').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
    </script>
</body>
</html>
