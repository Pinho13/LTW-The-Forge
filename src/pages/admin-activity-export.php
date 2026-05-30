<?php
declare(strict_types=1);
require_once(__DIR__ . '/../../utils/page_bootstrap.php');
require_once(__DIR__ . '/../../database/models/AdminLog.class.php');

[$session, $db] = requireAuthenticatedPage();

if (!$session->isAdmin()) {
    header('Location: /src/pages/my-account.php');
    exit;
}

$activity   = AdminLog::getAll($db);
$exportedAt = htmlspecialchars(trim($_GET['ts'] ?? date('j M Y, H:i')));
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Activity Log — The Forge</title>
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

        .export-header p {
            font-size: 11px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
        }

        thead tr {
            border-bottom: 1px solid #111;
        }

        thead th {
            text-align: left;
            padding: 6px 10px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #444;
        }

        tbody tr {
            border-bottom: 1px solid #e5e5e5;
        }

        tbody tr:last-child { border-bottom: none; }

        td {
            padding: 7px 10px;
            vertical-align: middle;
        }

        .col-time  { width: 14rem; color: #555; font-family: "Courier New", monospace; }
        .col-type  { width: 6rem; }
        .col-desc  { }
        .col-admin { width: 12rem; color: #555; text-align: right; }

        .badge {
            display: inline-block;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 2px 5px;
            border: 1px solid currentColor;
        }

        .badge--create  { color: #2a7a2a; }
        .badge--update  { color: #7a6000; }
        .badge--delete  { color: #8b0000; }
        .badge--login   { color: #555; }
        .badge--elevate { color: #7a4a00; }
        .badge--assign  { color: #7a6000; }

        .total {
            margin-top: 20px;
            font-size: 11px;
            color: #666;
            text-align: right;
        }

        @media print {
            .print-btn { display: none; }
            body { padding: 20px; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body>

    <div class="export-header">
        <div>
            <h1>The Forge — Admin Activity Log</h1>
        </div>
        <p>Exported <?= $exportedAt ?></p>
    </div>

    <?php if (empty($activity)): ?>
    <p>No activity logged yet.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th class="col-time">Time</th>
                <th class="col-type">Action</th>
                <th class="col-desc">Description</th>
                <th class="col-admin">Admin</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($activity as $row):
            $dt   = new DateTimeImmutable($row['created_at']);
            $timeLabel = $dt->format('Y-m-d H:i');
        ?>
            <tr>
                <td class="col-time"><?= htmlspecialchars($timeLabel) ?></td>
                <td class="col-type">
                    <span class="badge badge--<?= strtolower(htmlspecialchars($row['action_type'])) ?>">
                        <?= htmlspecialchars($row['action_type']) ?>
                    </span>
                </td>
                <td class="col-desc"><?= htmlspecialchars($row['description']) ?></td>
                <td class="col-admin"><?= htmlspecialchars($row['admin_name']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="total"><?= count($activity) ?> entries total</p>
    <?php endif; ?>

</body>
</html>
