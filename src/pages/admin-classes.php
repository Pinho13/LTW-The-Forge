<?php
declare(strict_types=1);
require_once(__DIR__ . '/../../utils/page_bootstrap.php');
require_once(__DIR__ . '/../../database/models/Enrollment.class.php');
require_once(__DIR__ . '/../../database/models/ClassCatalog.class.php');

[$session, $db] = requireAuthenticatedPage();

if (!$session->isAdmin()) {
    header('Location: /src/pages/my-account.php');
    exit;
}

$weekOffset = (int)($_GET['week'] ?? 0);
$today      = new DateTimeImmutable('today');
$monday     = $today->modify('Monday this week')->modify("{$weekOffset} weeks");
$sunday     = $monday->modify('+6 days');
$weekStart  = $monday->format('Y-m-d') . ' 00:00:00';
$weekEnd    = $sunday->format('Y-m-d') . ' 23:59:59';

$sessions  = Enrollment::getSessionsForWeekAdmin($db, $weekStart, $weekEnd);
$classes   = ClassCatalog::getAllClasses($db);
$types     = ClassCatalog::getAllTypes($db);
$trainers  = ClassCatalog::getAllTrainers($db);

$byDay = array_fill(0, 7, []);
foreach ($sessions as $s) {
    $dt  = new DateTimeImmutable($s['datetime']);
    $dow = (int)$dt->format('N') - 1;
    $byDay[$dow][] = $s;
}

const GRID_START_HOUR = 8;
const GRID_ROWS       = 52;

function timeToGridRow(string $datetime): int {
    $dt     = new DateTimeImmutable($datetime);
    $hour   = (int)$dt->format('G');
    $min    = (int)$dt->format('i');
    $offset = ($hour - GRID_START_HOUR) * 4 + intdiv($min, 15);
    return max(1, $offset + 1);
}
function durationToGridSpan(int $minutes): int {
    return max(1, intdiv($minutes, 15));
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
    <title>Classes (Admin) - The Forge</title>
    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/layout.css">
    <link rel="stylesheet" href="../style/classes.css">
    <link rel="stylesheet" href="../style/admin-classes.css">
</head>
<body>
    <?php $activePage = 'admin-classes'; include '../components/side-menu.php'; ?>

    <main>
        <?php include '../components/flash-messages.php'; ?>

        <header>
            <h1>Classes</h1>
            <div class="admin-classes-header-actions">
                <button type="button" class="news-header-btn" id="new-session-btn">+ New Session</button>
                <button type="button" class="news-header-btn" id="new-class-btn">+ New Class</button>
            </div>
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
                        $span  = durationToGridSpan((int)$group[0]['duration_minutes']);
                        $count = count($group);
                        $single = $count === 1;
                    ?>
                    <?php if ($single): $s = $group[0];
                        $start     = new DateTimeImmutable($s['datetime']);
                        $end       = $start->modify('+' . $s['duration_minutes'] . ' minutes');
                        $timeLabel = $start->format('H:i') . ' – ' . $end->format('H:i');
                        $spotsLeft = max(0, (int)$s['capacity'] - (int)$s['enrolled_count']);
                    ?>
                    <article class="class-card class-card--admin"
                             style="grid-row: <?= $row ?> / span <?= $span ?>"
                             data-session-id="<?= (int)$s['session_id'] ?>"
                             data-class-id="<?= (int)$s['class_id'] ?>"
                             data-class-name="<?= htmlspecialchars($s['class_name']) ?>"
                             data-trainer-id="<?= (int)$s['trainer_id'] ?>"
                             data-trainer="<?= htmlspecialchars($s['trainer_name'] ?? '') ?>"
                             data-room="<?= htmlspecialchars($s['room']) ?>"
                             data-datetime="<?= htmlspecialchars($s['datetime']) ?>"
                             data-duration="<?= (int)$s['duration_minutes'] ?>"
                             data-intensity="<?= (int)$s['intensity'] ?>"
                             data-capacity="<?= (int)$s['capacity'] ?>"
                             data-enrolled="<?= (int)$s['enrolled_count'] ?>"
                             data-type-id="<?= (int)$s['type_id'] ?>"
                             data-type="<?= htmlspecialchars($s['type_name'] ?? '') ?>"
                             data-description="<?= htmlspecialchars($s['description'] ?? '', ENT_QUOTES) ?>">
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
                            <span><?= $spotsLeft ?>/<?= (int)$s['capacity'] ?></span>
                        </footer>
                    </article>

                    <?php else: /* stack */ ?>
                    <div class="class-stack" style="grid-row: <?= $row ?> / span <?= $span ?>" data-index="0">
                        <?php foreach ($group as $si => $s):
                            $start     = new DateTimeImmutable($s['datetime']);
                            $end       = $start->modify('+' . $s['duration_minutes'] . ' minutes');
                            $timeLabel = $start->format('H:i') . ' – ' . $end->format('H:i');
                            $spotsLeft = max(0, (int)$s['capacity'] - (int)$s['enrolled_count']);
                            $cardClass = 'class-card class-card--admin class-stack__card' . ($si === 0 ? ' class-stack__card--active' : '');
                        ?>
                        <article class="<?= $cardClass ?>"
                                 data-session-id="<?= (int)$s['session_id'] ?>"
                                 data-class-id="<?= (int)$s['class_id'] ?>"
                                 data-class-name="<?= htmlspecialchars($s['class_name']) ?>"
                                 data-trainer-id="<?= (int)$s['trainer_id'] ?>"
                                 data-trainer="<?= htmlspecialchars($s['trainer_name'] ?? '') ?>"
                                 data-room="<?= htmlspecialchars($s['room']) ?>"
                                 data-datetime="<?= htmlspecialchars($s['datetime']) ?>"
                                 data-duration="<?= (int)$s['duration_minutes'] ?>"
                                 data-intensity="<?= (int)$s['intensity'] ?>"
                                 data-capacity="<?= (int)$s['capacity'] ?>"
                                 data-enrolled="<?= (int)$s['enrolled_count'] ?>"
                                 data-type-id="<?= (int)$s['type_id'] ?>"
                                 data-type="<?= htmlspecialchars($s['type_name'] ?? '') ?>"
                                 data-description="<?= htmlspecialchars($s['description'] ?? '', ENT_QUOTES) ?>">
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
                                <span><?= $spotsLeft ?>/<?= (int)$s['capacity'] ?></span>
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

    <!-- Edit session/class modal -->
    <dialog id="admin-class-modal" class="auth-modal class-modal">
        <button type="button" class="btn-ghost auth-modal__close" id="admin-modal-close">&times;</button>
        <h2 class="auth-modal__title" id="admin-modal-title"></h2>

        <div class="admin-modal-tabs">
            <button type="button" class="admin-modal-tab admin-modal-tab--active" data-tab="session">Session</button>
            <button type="button" class="admin-modal-tab" data-tab="class">Class</button>
        </div>

        <!-- Session tab -->
        <div class="admin-modal-panel" id="tab-session">
            <form id="form-session" class="auth-modal__form">
                <input type="hidden" name="csrf_token" value="<?= $session->getCsrfToken() ?>">
                <input type="hidden" name="session_id" id="edit-session-id">
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
                <div class="admin-modal-actions">
                    <button type="submit" class="btn-primary modal-action-btn" id="save-session-btn">Save Session</button>
                    <button type="button" class="btn-danger modal-action-btn" id="delete-session-btn">Delete Session</button>
                </div>
            </form>
        </div>

        <!-- Class tab -->
        <div class="admin-modal-panel" id="tab-class" hidden>
            <form id="form-class" class="auth-modal__form">
                <input type="hidden" name="csrf_token" value="<?= $session->getCsrfToken() ?>">
                <input type="hidden" name="class_id" id="edit-class-id">
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
                <div class="admin-modal-actions">
                    <button type="submit" class="btn-primary modal-action-btn" id="save-class-btn">Save Class</button>
                    <button type="button" class="btn-danger modal-action-btn" id="delete-class-btn">Delete Class</button>
                </div>
            </form>
        </div>
    </dialog>

    <!-- New session modal -->
    <dialog id="new-session-modal" class="auth-modal">
        <button type="button" class="btn-ghost auth-modal__close" id="new-session-close">&times;</button>
        <h2 class="auth-modal__title">New Session</h2>
        <form id="form-new-session" class="auth-modal__form">
            <input type="hidden" name="csrf_token" value="<?= $session->getCsrfToken() ?>">
            <label for="ns-class">Class</label>
            <select id="ns-class" name="class_id" required>
                <?php foreach ($classes as $cl): ?>
                <option value="<?= (int)$cl['id'] ?>"><?= htmlspecialchars($cl['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <label>Date &amp; Time</label>
            <div class="datetime-split">
                <input type="date" id="ns-date" required>
                <select id="ns-hour">
                    <?php for ($h = 8; $h <= 22; $h++): ?>
                    <option value="<?= $h ?>"><?= sprintf('%02d:00', $h) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <label for="ns-room">Room</label>
            <select id="ns-room" name="room" required></select>
            <label for="ns-capacity">Capacity</label>
            <input type="number" id="ns-capacity" name="capacity" min="1" required>
            <p class="auth-modal__error" id="new-session-error"></p>
            <button type="submit" class="btn-primary modal-action-btn">Create Session</button>
        </form>
    </dialog>

    <!-- New class modal -->
    <dialog id="new-class-modal" class="auth-modal">
        <button type="button" class="btn-ghost auth-modal__close" id="new-class-close">&times;</button>
        <h2 class="auth-modal__title">New Class</h2>
        <form id="form-new-class" class="auth-modal__form">
            <input type="hidden" name="csrf_token" value="<?= $session->getCsrfToken() ?>">
            <label for="nc-name">Name</label>
            <input type="text" id="nc-name" name="name" required maxlength="150">
            <label for="nc-type">Type</label>
            <select id="nc-type" name="type_id" required>
                <?php foreach ($types as $t): ?>
                <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <label for="nc-trainer">Trainer</label>
            <select id="nc-trainer" name="trainer_id">
                <option value="">— None —</option>
                <?php foreach ($trainers as $tr): ?>
                <option value="<?= (int)$tr['id'] ?>"><?= htmlspecialchars($tr['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <label for="nc-duration">Duration (minutes)</label>
            <input type="number" id="nc-duration" name="duration_minutes" min="1" required>
            <label for="nc-intensity">Intensity (1–5)</label>
            <input type="number" id="nc-intensity" name="intensity" min="1" max="5" required>
            <label for="nc-description">Description</label>
            <textarea id="nc-description" name="description" rows="3" maxlength="1000"></textarea>
            <p class="auth-modal__error" id="new-class-error"></p>
            <button type="submit" class="btn-primary modal-action-btn">Create Class</button>
        </form>
    </dialog>

    <dialog id="admin-confirm-modal" class="auth-modal">
        <h2 class="auth-modal__title" id="admin-confirm-title">Confirm</h2>
        <p class="news-delete-hint" id="admin-confirm-msg"></p>
        <div class="news-confirm-actions">
            <button type="button" class="btn-ghost btn-sm" id="admin-confirm-cancel">Cancel</button>
            <button type="button" class="btn-danger btn-sm" id="admin-confirm-ok">Delete</button>
        </div>
    </dialog>

    <script>
        const CSRF_TOKEN = <?= json_encode($session->getCsrfToken()) ?>;
        const CLASS_LIST = <?= json_encode(array_values($classes)) ?>;
    </script>
    <script src="../scripts/classes.js"></script>
    <script src="../scripts/admin-classes.js"></script>
</body>
</html>
