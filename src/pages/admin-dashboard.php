<?php
declare(strict_types=1);
require_once(__DIR__ . '/../../utils/page_bootstrap.php');
require_once(__DIR__ . '/../../database/models/AdminLog.class.php');

[$session, $db] = requireAuthenticatedPage();

if (!$session->isAdmin()) {
    header('Location: /src/pages/my-account.php');
    exit;
}

$stats    = AdminLog::getStats($db);
$activity = AdminLog::getRecent($db, 5);
$attention = AdminLog::getAttentionItems($db);
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
</head>
<body>
    <?php $activePage = 'account'; include '../components/side-menu.php'; ?>

    <main>
        <?php include '../components/flash-messages.php'; ?>

        <header>
            <h1>Dashboard</h1>
        </header>

        <!-- ── Stat cards ── -->
        <section class="dash-stats">

            <div class="dash-stat">
                <p class="dash-stat__label">Active Members</p>
                <p class="dash-stat__value"><?= $stats['active_members'] ?></p>
                <?php if ($stats['new_members_month'] > 0): ?>
                <p class="dash-stat__sub dash-stat__sub--green">+<?= $stats['new_members_month'] ?> this month</p>
                <?php else: ?>
                <p class="dash-stat__sub">&nbsp;</p>
                <?php endif; ?>
            </div>

            <div class="dash-stat">
                <p class="dash-stat__label">Active Trainers</p>
                <p class="dash-stat__value"><?= $stats['active_trainers'] ?></p>
                <p class="dash-stat__sub"><?= $stats['total_trainers'] ?> on roster</p>
            </div>

            <div class="dash-stat">
                <p class="dash-stat__label">Live Classes / Week</p>
                <p class="dash-stat__value"><?= $stats['sessions_this_week'] ?></p>
                <?php if ($stats['sessions_draft'] > 0): ?>
                <p class="dash-stat__sub dash-stat__sub--orange"><?= $stats['sessions_draft'] ?> without trainer</p>
                <?php else: ?>
                <p class="dash-stat__sub">All assigned</p>
                <?php endif; ?>
            </div>

            <div class="dash-stat">
                <p class="dash-stat__label">Equipment Ready</p>
                <p class="dash-stat__value">
                    <?= $stats['equipment_ready'] ?><span class="dash-stat__denom">/ <?= $stats['equipment_total'] ?></span>
                </p>
                <?php if ($stats['equipment_maintenance'] > 0): ?>
                <p class="dash-stat__sub dash-stat__sub--orange"><?= $stats['equipment_maintenance'] ?> in maintenance</p>
                <?php else: ?>
                <p class="dash-stat__sub dash-stat__sub--green">All operational</p>
                <?php endif; ?>
            </div>

        </section>

        <!-- ── Lower panels ── -->
        <div class="dash-panels">

            <!-- Needs attention -->
            <section class="dash-panel dash-panel--attention">
                <header class="dash-panel__header">
                    <h2 class="dash-panel__title">Needs Attention</h2>
                    <?php if (count($attention) > 3): ?>
                    <button type="button" class="dash-panel__action dash-panel__action--alert" id="attention-toggle">
                        +<?= count($attention) - 3 ?> more
                    </button>
                    <?php endif; ?>
                </header>

                <?php if (empty($attention)): ?>
                <p class="dash-empty">All clear — nothing needs attention right now.</p>
                <?php else: ?>
                <ul class="attention-list">
                    <?php foreach ($attention as $i => $item): ?>
                    <li class="attention-item attention-item--<?= htmlspecialchars($item['color']) ?><?= $i >= 3 ? ' attention-item--hidden' : '' ?>">
                        <span class="attention-item__icon"><?= htmlspecialchars($item['icon']) ?></span>
                        <div class="attention-item__body">
                            <p class="attention-item__title"><?= htmlspecialchars($item['title']) ?></p>
                            <p class="attention-item__detail"><?= htmlspecialchars($item['detail']) ?></p>
                        </div>
                        <a href="<?= htmlspecialchars($item['href']) ?>" class="attention-item__btn"><?= htmlspecialchars($item['action']) ?></a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </section>

        </div>

        <!-- ── Recent activity ── -->
        <section class="dash-panel dash-panel--activity">
            <header class="dash-panel__header">
                <h2 class="dash-panel__title">Recent Admin Activity</h2>
                <button type="button" class="dash-panel__action" id="export-btn">Export</button>
            </header>

            <?php if (empty($activity)): ?>
            <p class="dash-empty">No admin activity logged yet.</p>
            <?php else: ?>
            <table class="activity-table">
                <?php
                $prevDate = null;
                foreach ($activity as $row):
                    $dt   = new DateTimeImmutable($row['created_at']);
                    $date = $dt->format('Y-m-d');
                    $today = date('Y-m-d');
                    $yesterday = date('Y-m-d', strtotime('-1 day'));
                    $timeLabel = $date === $today ? $dt->format('H:i') . ' today'
                        : ($date === $yesterday ? $dt->format('H:i') . ' yesterday' : $dt->format('H:i M j'));
                ?>
                <tr class="activity-row">
                    <td class="activity-row__time"><?= htmlspecialchars($timeLabel) ?></td>
                    <td class="activity-row__type">
                        <span class="activity-badge activity-badge--<?= strtolower(htmlspecialchars($row['action_type'])) ?>">
                            <?= htmlspecialchars($row['action_type']) ?>
                        </span>
                    </td>
                    <td class="activity-row__desc"><?= htmlspecialchars($row['description']) ?></td>
                    <td class="activity-row__admin"><?= htmlspecialchars($row['admin_name']) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
        </section>

    </main>

    <?php include '../components/footer.php'; ?>

    <script>
    document.getElementById('export-btn').addEventListener('click', () => {
        const now = new Date();
        const ts  = now.toLocaleString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        const iframe = document.createElement('iframe');
        iframe.style.cssText = 'position:fixed;width:0;height:0;border:0;visibility:hidden';
        iframe.src = '/src/pages/admin-activity-export.php?ts=' + encodeURIComponent(ts);
        document.body.appendChild(iframe);
        iframe.onload = () => {
            iframe.contentWindow.print();
            iframe.contentWindow.addEventListener('afterprint', () => iframe.remove());
        };
    });

    const toggle = document.getElementById('attention-toggle');
    if (toggle) {
        const extra = Array.from(document.querySelectorAll('.attention-item--hidden'));
        const count = extra.length;
        toggle.addEventListener('click', () => {
            const expanded = toggle.dataset.expanded === '1';
            if (expanded) {
                extra.forEach(el => el.classList.add('attention-item--hidden'));
                toggle.textContent = '+' + count + ' more';
                toggle.dataset.expanded = '0';
            } else {
                extra.forEach(el => el.classList.remove('attention-item--hidden'));
                toggle.textContent = 'Collapse';
                toggle.dataset.expanded = '1';
            }
        });
    }
    </script>
</body>
</html>
