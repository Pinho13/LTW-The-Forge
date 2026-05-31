<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/page_bootstrap.php');
require_once(__DIR__ . '/../../database/models/Enrollment.class.php');

[$session, $db] = requireAuthenticatedPage();

if ($session->isMember() && !$session->isPremium()) {
    header('Location: /src/pages/my-account.php');
    exit;
}

$memberId = $session->getId();

// Week navigation: offset in weeks from current week (Mon-based)
$weekOffset = (int) ($_GET['week'] ?? 0);
$startParam = $_GET['start'] ?? '';
$startDate  = $startParam !== '' ? DateTimeImmutable::createFromFormat('Y-m-d', $startParam) : false;
if ($startDate === false) $startDate = null;

// Compute Monday of the displayed week
$today      = new DateTimeImmutable('today');
$anchorDate = $startDate ?? $today->modify("{$weekOffset} weeks");
$monday     = $anchorDate->modify('Monday this week');
$sunday     = $monday->modify('+6 days');

$weekStart = $monday->format('Y-m-d') . ' 00:00:00';
$weekEnd   = $sunday->format('Y-m-d') . ' 23:59:59';

$sessions  = Enrollment::getSessionsForWeek($db, $memberId, $weekStart, $weekEnd);

// Group sessions by day-of-week (0=Mon ... 6=Sun)
$byDay = array_fill(0, 7, []);
foreach ($sessions as $s) {
    $dt  = new DateTimeImmutable($s['datetime']);
    $dow = (int) $dt->format('N') - 1; // 0=Mon, 6=Sun
    $byDay[$dow][] = $s;
}

$mobileStart = $startDate ?? $anchorDate;
$mobileEnd   = $mobileStart->modify('+2 days');

$mobileStartTs = $mobileStart->format('Y-m-d') . ' 00:00:00';
$mobileEndTs   = $mobileEnd->format('Y-m-d') . ' 23:59:59';
$mobileSessions = Enrollment::getSessionsForWeek($db, $memberId, $mobileStartTs, $mobileEndTs);

$mobileByDay = array_fill(0, 3, []);
foreach ($mobileSessions as $s) {
    $dt   = new DateTimeImmutable($s['datetime']);
    $diff = (int) $dt->diff($mobileStart)->format('%r%a');
    if ($diff >= 0 && $diff < 3) {
        $mobileByDay[$diff][] = $s;
    }
}

// Calendar grid: 8 AM start, each row = 15 min
const GRID_START_HOUR = 8;
const GRID_ROWS       = 52; // 13 hours * 4

function timeToGridRow(string $datetime): int {
    $dt      = new DateTimeImmutable($datetime);
    $hour    = (int) $dt->format('G');
    $min     = (int) $dt->format('i');
    $offset  = ($hour - GRID_START_HOUR) * 4 + intdiv($min, 15);
    return max(1, $offset + 1);
}

function durationToGridSpan(int $minutes): int {
    return max(1, intdiv($minutes, 15));
}

$dayLabels = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
$todayDow  = (int) $today->format('N') - 1;
$todayDate = $today->format('Y-m-d');
$prevWeekStart  = $monday->modify('-7 days')->format('Y-m-d');
$nextWeekStart  = $monday->modify('+7 days')->format('Y-m-d');
$prevMobileStart = $mobileStart->modify('-3 days')->format('Y-m-d');
$nextMobileStart = $mobileStart->modify('+3 days')->format('Y-m-d');

$grids = [
    [
        'class' => 'calendar-grid--week',
        'start' => $monday,
        'days'  => 7,
        'byDay' => $byDay,
    ],
    [
        'class' => 'calendar-grid--mobile',
        'start' => $mobileStart,
        'days'  => 3,
        'byDay' => $mobileByDay,
    ],
];
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classes - The Forge</title>
    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/layout.css">
    <link rel="stylesheet" href="../style/classes.css">
</head>

<body>
    <?php $activePage = 'classes'; include '../components/side-menu.php'; ?>

    <main>
        <?php include '../components/flash-messages.php'; ?>

        <header>
            <h1>Classes</h1>
        </header>

        <section class="calendar">

            <nav class="calendar-nav" aria-label="Schedule navigation">
                <a href="?start=<?= urlencode($prevWeekStart) ?>" class="btn-ghost calendar-nav__week">&#8592; Prev</a>
                <h2 class="calendar-nav__range calendar-nav__week"><?= $monday->format('M j') ?> &ndash; <?= $sunday->format('M j, Y') ?></h2>
                <a href="?start=<?= urlencode($nextWeekStart) ?>" class="btn-ghost calendar-nav__week">Next &#8594;</a>

                <a href="?start=<?= urlencode($prevMobileStart) ?>" class="btn-ghost calendar-nav__mobile">&#8592; Prev</a>
                <h2 class="calendar-nav__range calendar-nav__mobile"><?= $mobileStart->format('M j') ?> &ndash; <?= $mobileEnd->format('M j, Y') ?></h2>
                <a href="?start=<?= urlencode($nextMobileStart) ?>" class="btn-ghost calendar-nav__mobile">Next &#8594;</a>
            </nav>

            <?php foreach ($grids as $grid): ?>
            <div class="calendar-grid <?= $grid['class'] ?>">

                <div class="calendar-time-spacer"></div>

                <?php for ($d = 0; $d < $grid['days']; $d++):
                    $dayDate    = $grid['start']->modify("+{$d} days");
                    $isToday    = $dayDate->format('Y-m-d') === $todayDate;
                    $labelIndex = (int) $dayDate->format('N') - 1;
                ?>
                <div class="calendar-day-header <?= $isToday ? 'today' : '' ?>">
                    <span class="day-name"><?= $dayLabels[$labelIndex] ?></span>
                    <span class="day-date"><?= $dayDate->format('j') ?></span>
                </div>
                <?php endfor; ?>

                <div class="calendar-time-gutter">
                    <?php for ($h = GRID_START_HOUR; $h < GRID_START_HOUR + 13; $h++): ?>
                        <div class="calendar-time-label"><?= $h <= 12 ? $h . ' AM' : ($h - 12) . ' PM' ?></div>
                    <?php endfor; ?>
                </div>

                <?php for ($d = 0; $d < $grid['days']; $d++): ?>
                <div class="calendar-day-column">
                    <?php
                    // Group this day's sessions by grid row to detect overlaps
                    $byRow = [];
                    foreach ($grid['byDay'][$d] as $s) {
                        $byRow[timeToGridRow($s['datetime'])][] = $s;
                    }
                    foreach ($byRow as $row => $group):
                        usort($group, fn($a, $b) => strcmp($a['class_name'], $b['class_name']));
                        $span   = durationToGridSpan((int) $group[0]['duration_minutes']);
                        $count  = count($group);
                        $single = $count === 1;
                    ?>
                    <?php if ($single): $s = $group[0];
                        $start     = new DateTimeImmutable($s['datetime']);
                        $end       = $start->modify('+' . $s['duration_minutes'] . ' minutes');
                        $timeLabel = $start->format('H:i') . ' – ' . $end->format('H:i');
                        $spotsLeft = max(0, (int)$s['capacity'] - (int)$s['enrolled_count']);
                        $isFull    = $spotsLeft === 0;
                        $status    = $s['member_status'];
                        $cardClass = 'class-card';
                        if ($status === 'enrolled')       $cardClass .= ' class-card--enrolled';
                        elseif ($status === 'waitlisted') $cardClass .= ' class-card--waitlisted';
                        elseif ($isFull)                  $cardClass .= ' class-card--full';
                    ?>
                    <article class="<?= $cardClass ?>"
                             style="grid-row: <?= $row ?> / span <?= $span ?>"
                             data-session-id="<?= (int)$s['session_id'] ?>"
                             data-class-name="<?= htmlspecialchars($s['class_name']) ?>"
                             data-trainer="<?= htmlspecialchars($s['trainer_name'] ?? 'TBA') ?>"
                             data-room="<?= htmlspecialchars($s['room']) ?>"
                             data-time="<?= htmlspecialchars($timeLabel) ?>"
                             data-intensity="<?= (int)$s['intensity'] ?>"
                             data-spots="<?= $spotsLeft ?>"
                             data-capacity="<?= (int)$s['capacity'] ?>"
                             data-status="<?= htmlspecialchars($status ?? '') ?>"
                             data-waitlist-position="<?= $status === 'waitlisted' ? (int)$s['waitlist_position'] : '' ?>"
                             data-type="<?= htmlspecialchars($s['type_name'] ?? '') ?>"
                             data-avg-rating="<?= $s['avg_rating'] !== null ? (float)$s['avg_rating'] : '' ?>"
                             data-review-count="<?= (int)$s['review_count'] ?>">
                        <header>
                            <h3><?= htmlspecialchars($s['class_name']) ?></h3>
                            <p><?= htmlspecialchars($timeLabel) ?></p>
                        </header>
                        <footer>
                            <div class="intensity-dots">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="intensity-dot <?= $i <= (int)$s['intensity'] ? 'filled' : '' ?>"></span>
                                <?php endfor; ?>
                            </div>
                            <?php if ($status === 'enrolled'): ?>
                                <span class="card-status">Enrolled</span>
                            <?php elseif ($status === 'waitlisted'): ?>
                                <span class="card-status card-status--wait">Waitlisted</span>
                            <?php elseif ($isFull): ?>
                                <span>Full</span>
                            <?php else: ?>
                                <span><?= $spotsLeft ?> left</span>
                            <?php endif; ?>
                        </footer>
                    </article>

                    <?php else: /* Multiple sessions at same time — stack card */ ?>
                    <div class="class-stack"
                         style="grid-row: <?= $row ?> / span <?= $span ?>"
                         data-index="0">
                        <?php foreach ($group as $si => $s):
                            $start     = new DateTimeImmutable($s['datetime']);
                            $end       = $start->modify('+' . $s['duration_minutes'] . ' minutes');
                            $timeLabel = $start->format('H:i') . ' – ' . $end->format('H:i');
                            $spotsLeft = max(0, (int)$s['capacity'] - (int)$s['enrolled_count']);
                            $isFull    = $spotsLeft === 0;
                            $status    = $s['member_status'];
                            $cardClass = 'class-card class-stack__card';
                            if ($si === 0) $cardClass .= ' class-stack__card--active';
                            if ($status === 'enrolled')       $cardClass .= ' class-card--enrolled';
                            elseif ($status === 'waitlisted') $cardClass .= ' class-card--waitlisted';
                            elseif ($isFull)                  $cardClass .= ' class-card--full';
                        ?>
                        <article class="<?= $cardClass ?>"
                                 data-session-id="<?= (int)$s['session_id'] ?>"
                                 data-class-name="<?= htmlspecialchars($s['class_name']) ?>"
                                 data-trainer="<?= htmlspecialchars($s['trainer_name'] ?? 'TBA') ?>"
                                 data-room="<?= htmlspecialchars($s['room']) ?>"
                                 data-time="<?= htmlspecialchars($timeLabel) ?>"
                                 data-intensity="<?= (int)$s['intensity'] ?>"
                                 data-spots="<?= $spotsLeft ?>"
                                 data-capacity="<?= (int)$s['capacity'] ?>"
                                 data-status="<?= htmlspecialchars($status ?? '') ?>"
                                 data-waitlist-position="<?= $status === 'waitlisted' ? (int)$s['waitlist_position'] : '' ?>"
                                 data-type="<?= htmlspecialchars($s['type_name'] ?? '') ?>"
                                 data-avg-rating="<?= $s['avg_rating'] !== null ? (float)$s['avg_rating'] : '' ?>"
                                 data-review-count="<?= (int)$s['review_count'] ?>">
                            <header>
                                <h3><?= htmlspecialchars($s['class_name']) ?></h3>
                                <p><?= htmlspecialchars($timeLabel) ?></p>
                            </header>
                            <footer>
                                <div class="intensity-dots">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="intensity-dot <?= $i <= (int)$s['intensity'] ? 'filled' : '' ?>"></span>
                                    <?php endfor; ?>
                                </div>
                                <?php if ($status === 'enrolled'): ?>
                                    <span class="card-status">Enrolled</span>
                                <?php elseif ($status === 'waitlisted'): ?>
                                    <span class="card-status card-status--wait">Waitlisted</span>
                                <?php elseif ($isFull): ?>
                                    <span>Full</span>
                                <?php else: ?>
                                    <span><?= $spotsLeft ?> left</span>
                                <?php endif; ?>
                            </footer>
                        </article>
                        <?php endforeach; ?>
                        <div class="class-stack__dots">
                            <?php for ($si = 0; $si < $count; $si++): ?>
                                <span class="class-stack__dot <?= $si === 0 ? 'class-stack__dot--active' : '' ?>"></span>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php endforeach; ?>
                </div>
                <?php endfor; ?>

            </div>
            <?php endforeach; ?>
        </section>
    </main>

    <?php include '../components/footer.php'; ?>

    <!-- Class detail modal -->
    <div class="modal-backdrop" id="page-backdrop"></div>

    <dialog id="class-modal" class="auth-modal class-modal">
        <button type="button" class="btn-ghost auth-modal__close" id="class-modal-close">&times;</button>
        <h2 class="auth-modal__title" id="modal-class-name"></h2>
        <p class="class-modal__meta" id="modal-meta"></p>
        <div class="class-modal__info">
            <span id="modal-type" class="class-modal__type"></span>
            <div class="intensity-dots" id="modal-intensity"></div>
        </div>
        <p class="class-modal__spots" id="modal-spots"></p>
        <div class="class-modal__rating" id="modal-rating"></div>
        <div id="modal-action-area"></div>
    </dialog>

    <script>
        const CSRF_TOKEN = <?= json_encode($session->getCsrfToken()) ?>;
    </script>
    <script src="../scripts/classes.js"></script>
</body>
</html>
