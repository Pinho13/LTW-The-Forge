<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/page_bootstrap.php');
require_once(__DIR__ . '/../../database/models/AdminAnalytics.class.php');

[$session, $db] = requireAuthenticatedPage();

if (!$session->isAdmin()) {
    header('Location: /src/pages/my-account.php');
    exit;
}

$topClasses       = AdminAnalytics::getTopClasses($db, 8);
$enrollmentTrend  = AdminAnalytics::getEnrollmentByMonth($db, 6);
$equipmentUsage   = AdminAnalytics::getEquipmentUsage($db);
$retention        = AdminAnalytics::getMemberRetention($db);
$typeDistribution = AdminAnalytics::getClassTypeDistribution($db);
$visitsByDay      = AdminAnalytics::getGymVisitsByDay($db);
$topTrainers      = AdminAnalytics::getTopTrainers($db, 5);
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - The Forge Admin</title>
    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/layout.css">
    <link rel="stylesheet" href="../style/admin-analytics.css">
</head>
<body>
    <?php $activePage = 'admin-analytics'; include '../components/side-menu.php'; ?>

    <main>
        <?php include '../components/flash-messages.php'; ?>

        <header>
            <h1>Analytics</h1>
        </header>

        <!-- ── Row 1: Enrollment trend + Retention ── -->
        <div class="analytics-row analytics-row--2col">

            <div class="analytics-panel">
                <div class="analytics-panel__header">
                    <h2 class="analytics-panel__title">Enrollment Trend</h2>
                    <span class="analytics-panel__sub">Last 6 months</span>
                </div>
                <div class="analytics-panel__body">
                    <canvas id="chart-enrollment" height="220"></canvas>
                </div>
            </div>

            <div class="analytics-panel">
                <div class="analytics-panel__header">
                    <h2 class="analytics-panel__title">Member Subscriptions</h2>
                    <span class="analytics-panel__sub">Current status</span>
                </div>
                <div class="analytics-panel__body analytics-panel__body--donut">
                    <div class="donut-canvas-wrap"><canvas id="chart-retention"></canvas></div>
                    <div class="donut-legend">
                        <div class="donut-legend__item donut-legend__item--active">
                            <span class="donut-legend__dot"></span>
                            <span class="donut-legend__label">Active</span>
                            <strong class="donut-legend__val"><?= $retention['active'] ?></strong>
                        </div>
                        <div class="donut-legend__item donut-legend__item--expired">
                            <span class="donut-legend__dot"></span>
                            <span class="donut-legend__label">Expired</span>
                            <strong class="donut-legend__val"><?= $retention['expired'] ?></strong>
                        </div>
                        <div class="donut-legend__item donut-legend__item--frozen">
                            <span class="donut-legend__dot"></span>
                            <span class="donut-legend__label">Frozen</span>
                            <strong class="donut-legend__val"><?= $retention['frozen'] ?></strong>
                        </div>
                        <div class="donut-legend__item donut-legend__item--cancelled">
                            <span class="donut-legend__dot"></span>
                            <span class="donut-legend__label">Cancelled</span>
                            <strong class="donut-legend__val"><?= $retention['cancelled'] ?></strong>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ── Row 2: Top classes ── -->
        <div class="analytics-row analytics-row--full">

            <div class="analytics-panel">
                <div class="analytics-panel__header">
                    <h2 class="analytics-panel__title">Most Popular Classes</h2>
                    <span class="analytics-panel__sub">By total enrollments</span>
                </div>
                <div class="analytics-panel__body">
                    <canvas id="chart-top-classes" height="200"></canvas>
                </div>
            </div>

        </div>

        <!-- ── Row 3: Equipment usage + Class type distribution ── -->
        <div class="analytics-row analytics-row--2col">

            <div class="analytics-panel">
                <div class="analytics-panel__header">
                    <h2 class="analytics-panel__title">Equipment Reservations</h2>
                    <span class="analytics-panel__sub">By machine type</span>
                </div>
                <div class="analytics-panel__body">
                    <canvas id="chart-equipment" height="220"></canvas>
                </div>
            </div>

            <div class="analytics-panel">
                <div class="analytics-panel__header">
                    <h2 class="analytics-panel__title">Class Type Popularity</h2>
                    <span class="analytics-panel__sub">Enrollments by category</span>
                </div>
                <div class="analytics-panel__body analytics-panel__body--donut">
                    <div class="donut-canvas-wrap"><canvas id="chart-types"></canvas></div>
                    <div class="donut-legend" id="type-legend"></div>
                </div>
            </div>

        </div>

        <!-- ── Row 4: Visit heatmap by weekday ── -->
        <div class="analytics-row analytics-row--full">

            <div class="analytics-panel">
                <div class="analytics-panel__header">
                    <h2 class="analytics-panel__title">Activity by Day of Week</h2>
                    <span class="analytics-panel__sub">Class attendances + equipment reservations</span>
                </div>
                <div class="analytics-panel__body">
                    <canvas id="chart-visits" height="180"></canvas>
                </div>
            </div>

        </div>

        <!-- ── Row 5: Top trainers table ── -->
        <div class="analytics-row analytics-row--full">

            <div class="analytics-panel">
                <div class="analytics-panel__header">
                    <h2 class="analytics-panel__title">Top Trainers</h2>
                    <span class="analytics-panel__sub">By student reach</span>
                </div>
                <div class="analytics-panel__body analytics-panel__body--table">
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th>Trainer</th>
                                <th class="col-num">Sessions</th>
                                <th class="col-num">Students</th>
                                <th class="col-num">Avg Rating</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($topTrainers as $i => $t): ?>
                            <tr>
                                <td>
                                    <span class="trainer-rank"><?= $i + 1 ?></span>
                                    <?= htmlspecialchars($t['name']) ?>
                                </td>
                                <td class="col-num"><?= (int)$t['sessions_taught'] ?></td>
                                <td class="col-num"><?= (int)$t['total_students'] ?></td>
                                <td class="col-num">
                                    <?php if ($t['avg_rating']): ?>
                                        <span class="rating-val"><?= number_format((float)$t['avg_rating'], 1) ?></span>
                                        <span class="rating-star">★</span>
                                    <?php else: ?>
                                        <span class="rating-none">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </main>

    <?php include '../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    (function () {
        /* ── Design tokens (match base.css vars exactly) ── */
        const GOLD        = '#A8843C';
        const LIGHT_GOLD  = '#D4A853';
        const GREY        = '#ACABA2';
        const ALMOST_WHITE= '#F2EDE4';
        const GRID_COLOR  = 'rgba(172,171,162,0.12)';
        const BG_DARK     = '#201E19';

        /* Gold for primary, descending greys for the rest */
        const PALETTE = [
            '#A8843C',  /* gold */
            '#C8C7BF',  /* light grey */
            '#8C8C7A',  /* mid grey */
            '#5A5A52',  /* dark grey */
            '#D4A853',  /* light gold */
            '#3A3A34',  /* near-black grey */
            '#B0AFA6',  /* grey-2 */
            '#6E6E64',  /* grey-3 */
        ];

        Chart.defaults.color = GREY;
        Chart.defaults.font.family = '"Instrument Sans", sans-serif';
        Chart.defaults.font.size = 11;

        const baseAxes = {
            x: { grid: { color: GRID_COLOR }, ticks: { color: GREY } },
            y: { grid: { color: GRID_COLOR }, ticks: { color: GREY }, beginAtZero: true }
        };

        /* ── 1. Enrollment Trend (line) ── */
        const enrollData = <?= json_encode(array_values($enrollmentTrend)) ?>;
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

        /* ── 2. Retention donut ── */
        const ret = <?= json_encode($retention) ?>;
        new Chart(document.getElementById('chart-retention'), {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Expired', 'Frozen', 'Cancelled'],
                datasets: [{
                    data: [ret.active, ret.expired, ret.frozen, ret.cancelled],
                    backgroundColor: [GOLD, '#5A5A52', LIGHT_GOLD, '#3A3A34'],
                    borderColor: BG_DARK,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}` } }
                }
            }
        });

        /* ── 3. Top Classes (horizontal bar) ── */
        const classData = <?= json_encode(array_values($topClasses)) ?>;
        new Chart(document.getElementById('chart-top-classes'), {
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

        /* ── 4. Equipment Reservations (bar) ── */
        const eqData = <?= json_encode(array_values($equipmentUsage)) ?>;
        new Chart(document.getElementById('chart-equipment'), {
            type: 'bar',
            data: {
                labels: eqData.map(r => r.equipment_name),
                datasets: [
                    {
                        label: 'Reservations',
                        data: eqData.map(r => parseInt(r.reservation_count) || 0),
                        backgroundColor: GOLD + 'cc',
                        borderColor: GOLD,
                        borderWidth: 1
                    },
                    {
                        label: 'Units',
                        data: eqData.map(r => parseInt(r.unit_count) || 0),
                        backgroundColor: 'rgba(172,171,162,0.18)',
                        borderColor: GREY,
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { labels: { color: GREY, boxWidth: 10 } } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: ALMOST_WHITE, maxRotation: 30 } },
                    y: { grid: { color: GRID_COLOR }, ticks: { color: GREY }, beginAtZero: true }
                }
            }
        });

        /* ── 5. Class Type Distribution (doughnut) ── */
        const typeData = <?= json_encode(array_values($typeDistribution)) ?>;
        const typeLegend = document.getElementById('type-legend');
        new Chart(document.getElementById('chart-types'), {
            type: 'doughnut',
            data: {
                labels: typeData.map(r => r.type),
                datasets: [{
                    data: typeData.map(r => parseInt(r.total_enrolled) || 0),
                    backgroundColor: PALETTE.map(c => c + 'dd'),
                    borderColor: BG_DARK,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed} enrollments` } }
                }
            }
        });
        typeData.forEach((r, i) => {
            typeLegend.insertAdjacentHTML('beforeend', `
                <div class="donut-legend__item">
                    <span class="donut-legend__dot" style="background:${PALETTE[i % PALETTE.length]}"></span>
                    <span class="donut-legend__label">${r.type}</span>
                    <strong class="donut-legend__val">${r.total_enrolled || 0}</strong>
                </div>`);
        });

        /* ── 6. Gym Visits by Day (bar) ── */
        const visitsRaw = <?= json_encode(array_values($visitsByDay)) ?>;
        const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        new Chart(document.getElementById('chart-visits'), {
            type: 'bar',
            data: {
                labels: dayNames,
                datasets: [{
                    label: 'Visits',
                    data: visitsRaw,
                    backgroundColor: visitsRaw.map(v => {
                        const max = Math.max(...visitsRaw);
                        const ratio = max > 0 ? v / max : 0;
                        return `rgba(168,132,60,${0.2 + ratio * 0.7})`;
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

    })();
    </script>
</body>
</html>
