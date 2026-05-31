<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/page_bootstrap.php');
require_once(__DIR__ . '/../utils/calendar_helpers.php');
require_once(__DIR__ . '/../../database/models/Enrollment.class.php');
require_once(__DIR__ . '/../../database/models/ClassCatalog.class.php');

[$session, $db] = requireAuthenticatedPage();

if (!$session->isTrainer() && !$session->isAdmin()) {
    header('Location: /src/pages/my-account.php');
    exit;
}

$trainerId = $session->getId();

$weekOffset = (int)($_GET['week'] ?? 0);
$today      = new DateTimeImmutable('today');
$monday     = $today->modify('Monday this week')->modify("{$weekOffset} weeks");
$sunday     = $monday->modify('+6 days');
$weekStart  = $monday->format('Y-m-d') . ' 00:00:00';
$weekEnd    = $sunday->format('Y-m-d') . ' 23:59:59';

$sessions = Enrollment::getSessionsForWeekTrainer($db, $trainerId, $weekStart, $weekEnd);
$types    = ClassCatalog::getAllTypes($db);
$trainers = ClassCatalog::getAllTrainers($db);
$classes  = ClassCatalog::getAllClasses($db);

$byDay = array_fill(0, 7, []);
foreach ($sessions as $s) {
    $dt  = new DateTimeImmutable($s['datetime']);
    $dow = (int)$dt->format('N') - 1;
    $byDay[$dow][] = $s;
}

$dayLabels = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
$todayDow  = (int)$today->format('N') - 1;
$todayDate = $today->format('Y-m-d');
$prevWeek  = $weekOffset - 1;
$nextWeek  = $weekOffset + 1;
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Schedule - The Forge</title>
    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/layout.css">
    <link rel="stylesheet" href="../style/classes.css">
    <link rel="stylesheet" href="../style/admin-classes.css">
    <link rel="stylesheet" href="../style/trainer-schedule.css">
</head>
<body>
    <?php $activePage = 'trainer-schedule'; include '../components/side-menu.php'; ?>

    <main>
        <?php include '../components/flash-messages.php'; ?>

        <header>
            <h1>My Schedule</h1>
        </header>

        <section class="calendar">
            <nav class="calendar-nav" aria-label="Week navigation">
                <a href="?week=<?= $prevWeek ?>" class="btn-ghost">&#8592; Prev</a>
                <h2><?= $monday->format('M j') ?> &ndash; <?= $sunday->format('M j, Y') ?></h2>
                <a href="?week=<?= $nextWeek ?>" class="btn-ghost">Next &#8594;</a>
            </nav>

            <div class="calendar-grid">
                <div class="calendar-time-spacer"></div>

                <?php for ($d = 0; $d < 7; $d++):
                    $dayDate = $monday->modify("+{$d} days");
                    $isToday = $dayDate->format('Y-m-d') === $todayDate;
                ?>
                <div class="calendar-day-header <?= $isToday ? 'today' : '' ?>">
                    <span class="day-name"><?= $dayLabels[$d] ?></span>
                    <span class="day-date"><?= $dayDate->format('j') ?></span>
                </div>
                <?php endfor; ?>

                <div class="calendar-time-gutter">
                    <?php for ($h = GRID_START_HOUR; $h < GRID_START_HOUR + 13; $h++): ?>
                        <div class="calendar-time-label"><?= $h <= 12 ? $h . ' AM' : ($h - 12) . ' PM' ?></div>
                    <?php endfor; ?>
                </div>

                <?php for ($d = 0; $d < 7; $d++): ?>
                <div class="calendar-day-column">
                    <?php
                    $byRow = [];
                    foreach ($byDay[$d] as $s) {
                        $byRow[timeToGridRow($s['datetime'])][] = $s;
                    }
                    foreach ($byRow as $row => $group):
                        usort($group, fn($a, $b) => strcmp($a['class_name'], $b['class_name']));
                        $span   = durationToGridSpan((int)$group[0]['duration_minutes']);
                        $count  = count($group);
                        $single = $count === 1;
                    ?>
                    <?php if ($single): $s = $group[0];
                        $start     = new DateTimeImmutable($s['datetime']);
                        $end       = $start->modify('+' . $s['duration_minutes'] . ' minutes');
                        $timeLabel = $start->format('H:i') . ' – ' . $end->format('H:i');
                        $enrolled  = (int)$s['enrolled_count'];
                        $capacity  = (int)$s['capacity'];
                        $spotsLeft = max(0, $capacity - $enrolled);
                        $isFull    = $spotsLeft === 0;
                        $isMine    = (bool)$s['is_mine'];
                        $cardClass = 'class-card';
                        if ($isMine) $cardClass .= ' class-card--highlight';
                        else         $cardClass .= ' class-card--highlight-dim';
                        if ($isFull) $cardClass .= ' class-card--full';
                    ?>
                    <article class="<?= $cardClass ?>"
                             style="grid-row: <?= $row ?> / span <?= $span ?>"
                             data-session-id="<?= (int)$s['session_id'] ?>"
                             data-class-id="<?= (int)$s['class_id'] ?>"
                             data-class-name="<?= htmlspecialchars($s['class_name']) ?>"
                             data-trainer-id="<?= (int)($s['trainer_id'] ?? 0) ?>"
                             data-trainer="<?= htmlspecialchars($s['trainer_name'] ?? '') ?>"
                             data-room="<?= htmlspecialchars($s['room']) ?>"
                             data-time="<?= htmlspecialchars($timeLabel) ?>"
                             data-datetime="<?= htmlspecialchars($s['datetime']) ?>"
                             data-duration="<?= (int)$s['duration_minutes'] ?>"
                             data-intensity="<?= (int)$s['intensity'] ?>"
                             data-capacity="<?= $capacity ?>"
                             data-spots="<?= $spotsLeft ?>"
                             data-enrolled="<?= $enrolled ?>"
                             data-waitlisted="<?= (int)$s['waitlisted_count'] ?>"
                             data-type-id="<?= (int)$s['type_id'] ?>"
                             data-type="<?= htmlspecialchars($s['type_name'] ?? '') ?>"
                             data-description="<?= htmlspecialchars($s['description'] ?? '', ENT_QUOTES) ?>"
                             data-is-featured="<?= (int)$s['is_featured'] ?>"
                             data-status=""
                             data-avg-rating=""
                             data-review-count="0"
                             data-is-mine="<?= $isMine ? '1' : '0' ?>">
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
                            <?php if ($isMine): ?>
                                <span><?= $enrolled ?>/<?= $capacity ?></span>
                            <?php elseif ($isFull): ?>
                                <span>Full</span>
                            <?php else: ?>
                                <span><?= $spotsLeft ?> left</span>
                            <?php endif; ?>
                        </footer>
                    </article>

                    <?php else:
                        $anyMine  = array_any($group, fn($g) => (bool)$g['is_mine']);
                        $stackCls = 'class-stack' . ($anyMine ? '' : ' class-card--highlight-dim');
                    ?>
                    <div class="<?= $stackCls ?>" style="grid-row: <?= $row ?> / span <?= $span ?>" data-index="0">
                        <?php foreach ($group as $si => $s):
                            $start     = new DateTimeImmutable($s['datetime']);
                            $end       = $start->modify('+' . $s['duration_minutes'] . ' minutes');
                            $timeLabel = $start->format('H:i') . ' – ' . $end->format('H:i');
                            $enrolled  = (int)$s['enrolled_count'];
                            $capacity  = (int)$s['capacity'];
                            $spotsLeft = max(0, $capacity - $enrolled);
                            $isFull    = $spotsLeft === 0;
                            $isMine    = (bool)$s['is_mine'];
                            $cardClass = 'class-card class-stack__card';
                            if ($si === 0) $cardClass .= ' class-stack__card--active';
                            if ($isMine)   $cardClass .= ' class-card--highlight';
                            if ($isFull)   $cardClass .= ' class-card--full';
                        ?>
                        <article class="<?= $cardClass ?>"
                                 data-session-id="<?= (int)$s['session_id'] ?>"
                                 data-class-id="<?= (int)$s['class_id'] ?>"
                                 data-class-name="<?= htmlspecialchars($s['class_name']) ?>"
                                 data-trainer-id="<?= (int)($s['trainer_id'] ?? 0) ?>"
                                 data-trainer="<?= htmlspecialchars($s['trainer_name'] ?? '') ?>"
                                 data-room="<?= htmlspecialchars($s['room']) ?>"
                                 data-time="<?= htmlspecialchars($timeLabel) ?>"
                                 data-datetime="<?= htmlspecialchars($s['datetime']) ?>"
                                 data-duration="<?= (int)$s['duration_minutes'] ?>"
                                 data-intensity="<?= (int)$s['intensity'] ?>"
                                 data-capacity="<?= $capacity ?>"
                                 data-spots="<?= $spotsLeft ?>"
                                 data-enrolled="<?= $enrolled ?>"
                                 data-waitlisted="<?= (int)$s['waitlisted_count'] ?>"
                                 data-type-id="<?= (int)$s['type_id'] ?>"
                                 data-type="<?= htmlspecialchars($s['type_name'] ?? '') ?>"
                                 data-description="<?= htmlspecialchars($s['description'] ?? '', ENT_QUOTES) ?>"
                                 data-is-featured="<?= (int)$s['is_featured'] ?>"
                                 data-status=""
                                 data-avg-rating=""
                                 data-review-count="0"
                                 data-is-mine="<?= $isMine ? '1' : '0' ?>">
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
                                <?php if ($isMine): ?>
                                    <span><?= $enrolled ?>/<?= $capacity ?></span>
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
        </section>
    </main>

    <?php include '../components/footer.php'; ?>

    <div class="modal-backdrop" id="page-backdrop"></div>

    <!-- Action picker modal -->
    <dialog id="card-picker-modal" class="auth-modal feature-swap-modal">
        <button type="button" class="btn-ghost auth-modal__close" id="picker-close">&times;</button>
        <h2 class="auth-modal__title" id="picker-title"></h2>
        <p class="feature-swap-modal__hint" id="picker-meta"></p>
        <ul class="feature-swap-modal__list" id="picker-list">
            <li><button type="button" class="feature-swap-modal__pick" id="picker-edit-session">Edit Session</button></li>
            <li><button type="button" class="feature-swap-modal__pick" id="picker-edit-class">Edit Class</button></li>
            <li><button type="button" class="feature-swap-modal__pick" id="picker-roster">View Roster</button></li>
            <li><button type="button" class="feature-swap-modal__pick" id="picker-waitlist">View Waitlist</button></li>
        </ul>
    </dialog>

    <!-- Edit session/class modal (same as admin) -->
    <dialog id="admin-class-modal" class="auth-modal class-modal">
        <button type="button" class="btn-ghost auth-modal__close" id="admin-modal-close">&times;</button>
        <h2 class="auth-modal__title" id="admin-modal-title"></h2>
        <p class="auth-modal__subtitle" id="admin-modal-subtitle"></p>

        <div class="admin-modal-panel" id="tab-session">
            <form id="form-session" class="auth-modal__form">
                <input type="hidden" name="csrf_token" value="<?= $session->getCsrfToken() ?>">
                <input type="hidden" name="session_id" id="edit-session-id">
                <div class="admin-modal-fields">
                    <label>Date &amp; Time</label>
                    <div class="datetime-split">
                        <input type="date" id="edit-date" required>
                        <select id="edit-hour">
                            <?php for ($h = 8; $h <= 22; $h++): ?>
                            <option value="<?= $h ?>"><?= sprintf('%02d:00', $h) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <label for="edit-room">Room</label>
                    <select id="edit-room" name="room" required></select>
                    <label for="edit-capacity">Capacity</label>
                    <input type="number" id="edit-capacity" name="capacity" min="1" required>
                    <p class="admin-modal-enrolled" id="edit-enrolled-info"></p>
                    <p class="auth-modal__error" id="session-error"></p>
                </div>
                <div class="admin-modal-footer">
                    <div class="admin-modal-actions">
                        <button type="submit" class="btn-primary modal-action-btn" id="save-session-btn">Save Session</button>
                        <button type="button" class="btn-danger modal-action-btn" id="delete-session-btn">Delete Session</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="admin-modal-panel" id="tab-class" hidden>
            <form id="form-class" class="auth-modal__form">
                <input type="hidden" name="csrf_token" value="<?= $session->getCsrfToken() ?>">
                <input type="hidden" name="class_id" id="edit-class-id">
                <div class="admin-modal-fields">
                    <label for="edit-class-name">Name</label>
                    <input type="text" id="edit-class-name" name="name" required maxlength="150">
                    <label for="edit-class-type">Type</label>
                    <select id="edit-class-type" name="type_id">
                        <?php foreach ($types as $t): ?>
                        <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="edit-class-trainer">Trainer</label>
                    <select id="edit-class-trainer" name="trainer_id">
                        <option value="">— None —</option>
                        <?php foreach ($trainers as $tr): ?>
                        <option value="<?= (int)$tr['id'] ?>"><?= htmlspecialchars($tr['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="edit-class-duration">Duration (minutes)</label>
                    <input type="number" id="edit-class-duration" name="duration_minutes" min="1" required>
                    <label for="edit-class-intensity">Intensity (1–5)</label>
                    <input type="number" id="edit-class-intensity" name="intensity" min="1" max="5" required>
                    <label for="edit-class-description">Description</label>
                    <textarea id="edit-class-description" name="description" rows="3" maxlength="1000"></textarea>
                    <p class="auth-modal__error" id="class-error"></p>
                </div>
                <div class="admin-modal-footer">
                    <div class="admin-modal-actions">
                        <button type="submit" class="btn-primary modal-action-btn" id="save-class-btn">Save Class</button>
                        <button type="button" class="btn-danger modal-action-btn" id="delete-class-btn">Delete Class</button>
                    </div>
                </div>
            </form>
        </div>
    </dialog>

    <!-- Roster / waitlist modal -->
    <dialog id="roster-modal" class="auth-modal auth-modal--simple">
        <button type="button" class="btn-ghost auth-modal__close" id="roster-close">&times;</button>
        <h2 class="auth-modal__title" id="roster-title"></h2>
        <div id="roster-body" class="roster-body"></div>
    </dialog>

    <!-- Read-only class detail modal (for non-mine cards, used by classes.js) -->
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

    <!-- Confirm modal (for delete) -->
    <dialog id="admin-confirm-modal" class="auth-modal auth-modal--simple">
        <h2 class="auth-modal__title">Confirm</h2>
        <p class="news-delete-hint" id="admin-confirm-msg"></p>
        <div class="news-confirm-actions">
            <button type="button" class="btn-ghost btn-sm" id="admin-confirm-cancel">Cancel</button>
            <button type="button" class="btn-danger btn-sm" id="admin-confirm-ok">Delete</button>
        </div>
    </dialog>

    <script>
        const CSRF_TOKEN    = <?= json_encode($session->getCsrfToken()) ?>;
        const CLASS_LIST    = <?= json_encode(array_values($classes)) ?>;
        const ACTIVE_FILTER = null;
    </script>
    <script src="../scripts/classes.js"></script>
    <script src="../scripts/admin-classes.js"></script>
    <script src="../scripts/trainer-schedule.js"></script>
</body>
</html>
