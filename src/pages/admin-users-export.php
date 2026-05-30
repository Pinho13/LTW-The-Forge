<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/page_bootstrap.php');
require_once(__DIR__ . '/../../database/models/AdminUser.class.php');

[$session, $db] = requireAuthenticatedPage();

if (!$session->isAdmin()) {
    header('Location: /src/pages/my-account.php');
    exit;
}

$users      = AdminUser::getAll($db, '', '', 'all', 0);
$exportedAt = htmlspecialchars(trim($_GET['ts'] ?? date('j M Y, H:i')));
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title>Users Export — The Forge</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: "Helvetica Neue", Arial, sans-serif;
            font-size: 12px;
            color: #111;
            background: #fff;
            padding: 40px;
        }

        .export-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 24px;
            padding-bottom: 12px;
            border-bottom: 2px solid #111;
        }

        .export-header h1 {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .export-header p { font-size: 11px; color: #666; }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
        }

        thead tr { border-bottom: 1px solid #111; }

        thead th {
            text-align: left;
            padding: 6px 8px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #444;
        }

        tbody tr { border-bottom: 1px solid #e5e5e5; }
        tbody tr:last-child { border-bottom: none; }

        td { padding: 6px 8px; vertical-align: middle; }

        .col-name    { width: 16rem; }
        .col-email   { width: 18rem; color: #555; }
        .col-role    { width: 6rem; }
        .col-plan    { width: 8rem; }
        .col-expiry  { width: 9rem; }
        .col-status  { width: 6rem; }
        .col-joined  { width: 9rem; color: #555; text-align: right; }

        .badge {
            display: inline-block;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 2px 5px;
            border: 1px solid currentColor;
        }

        .badge--member  { color: #555; }
        .badge--trainer { color: #7a4a00; }
        .badge--admin   { color: #8b0000; }

        .status--active { color: #2a7a2a; }
        .status--banned { color: #8b0000; }
        .status--frozen { color: #555; }

        .expiry--expired { color: #8b0000; }

        .total {
            margin-top: 20px;
            font-size: 11px;
            color: #666;
            text-align: right;
        }

        @media print {
            body { padding: 20px; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body>

    <div class="export-header">
        <h1>The Forge — Users</h1>
        <p>Exported <?= $exportedAt ?></p>
    </div>

    <?php if (empty($users)): ?>
    <p>No users found.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th class="col-name">Name</th>
                <th class="col-email">Email</th>
                <th class="col-role">Role</th>
                <th class="col-plan">Plan</th>
                <th class="col-expiry">Expires</th>
                <th class="col-status">Status</th>
                <th class="col-joined">Joined</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u):
            $isBanned  = !(bool)$u['is_active'];
            $isFrozen  = !$isBanned && ($u['sub_status'] === 'frozen');
            $statusLabel = $isBanned ? 'Banned' : ($isFrozen ? 'Frozen' : 'Active');
            $statusClass = $isBanned ? 'banned' : ($isFrozen ? 'frozen' : 'active');
            $isExpired = $u['sub_end_date'] && $u['sub_end_date'] < date('Y-m-d');
        ?>
            <tr>
                <td class="col-name">
                    <strong><?= htmlspecialchars($u['name']) ?></strong>
                    <span style="color:#888"> @<?= htmlspecialchars($u['username']) ?></span>
                </td>
                <td class="col-email"><?= htmlspecialchars($u['email']) ?></td>
                <td class="col-role">
                    <span class="badge badge--<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span>
                </td>
                <td class="col-plan"><?= $u['plan_name'] ? htmlspecialchars($u['plan_name']) : '—' ?></td>
                <td class="col-expiry <?= $isExpired ? 'expiry--expired' : '' ?>">
                    <?= $u['sub_end_date'] ? date('j M Y', strtotime($u['sub_end_date'])) : '—' ?>
                </td>
                <td class="col-status status--<?= $statusClass ?>"><?= $statusLabel ?></td>
                <td class="col-joined"><?= date('j M Y', strtotime($u['created_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="total"><?= count($users) ?> users total</p>
    <?php endif; ?>

</body>
</html>
