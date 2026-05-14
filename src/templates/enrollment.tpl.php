<?php
declare(strict_types=1);

function drawEnrollmentItem(array $e, string $tab): void
{
    $start     = new DateTime($e['datetime']);
    $end       = (clone $start)->modify('+' . (int)$e['duration_minutes'] . ' minutes');
    $timeLabel = $start->format('D j M · H:i') . ' – ' . $end->format('H:i');
    ?>
    <li class="enrollment">
        <div class="enrollment__intensity">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <span class="intensity-dot <?= $i <= (int)$e['intensity'] ? 'filled' : '' ?>"></span>
            <?php endfor; ?>
        </div>

        <div class="enrollment__main">
            <h3 class="enrollment__name"><?= htmlspecialchars($e['class_name']) ?></h3>
            <p class="enrollment__meta"><?= htmlspecialchars($timeLabel) ?></p>
            <p class="enrollment__sub">
                <?= htmlspecialchars($e['room']) ?> · Trainer: <?= htmlspecialchars($e['trainer_name'] ?? 'TBA') ?>
            </p>
        </div>

        <div class="enrollment__actions">
            <?php if ($e['status'] === 'enrolled'): ?>
                <span class="status status--enrolled">Enrolled</span>
            <?php elseif ($e['status'] === 'waitlisted'): ?>
                <span class="status status--waitlisted">Waitlisted · #<?= (int)$e['waitlist_position'] ?></span>
            <?php elseif ($e['status'] === 'completed'): ?>
                <span class="status status--completed">Completed</span>
            <?php elseif ($e['status'] === 'missed'): ?>
                <span class="status status--missed">Missed</span>
            <?php endif; ?>

            <?php if ($tab === 'upcoming'): ?>
                <button type="button" class="btn-danger btn-sm"
                    data-cancel-id="<?= (int)$e['id'] ?>"
                    data-cancel-name="<?= htmlspecialchars($e['class_name']) ?>">
                    Cancel
                </button>
            <?php endif; ?>
        </div>
    </li>
    <?php
}
