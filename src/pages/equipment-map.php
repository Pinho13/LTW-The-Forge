<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/page_bootstrap.php');
require_once(__DIR__ . '/../../database/models/Equipment.class.php');

[$session, $db] = requireAuthenticatedPage();

$units = Equipment::getAllUnitsWithStatus($db);

$unitMap = [];
foreach ($units as $u) {
    $unitMap[(int)$u['id']] = [
        'id'             => (int)$u['id'],
        'equipment_id'   => (int)$u['equipment_id'],
        'identifier'     => $u['identifier'],
        'equipment_name' => $u['equipment_name'],
        'status'         => $u['status'],
        'is_available'   => (bool)$u['is_available'],
    ];
}
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Map - The Forge</title>
    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/layout.css">
    <link rel="stylesheet" href="../style/equipment-map.css">
</head>

<body>
    <?php $activePage = 'equipment'; include '../components/side-menu.php'; ?>

    <main>
        <?php include '../components/flash-messages.php'; ?>

        <header>
            <h1>Equipment</h1>
        </header>

        <div class="map-body">
            <div class="map-wrap">
                <svg class="gym-svg" viewBox="0 0 650 425"
                     xmlns="http://www.w3.org/2000/svg"
                     role="img" aria-label="Gym floor plan">

                    <image href="/database/assets/gym/gym-floor-plan-dark.png"
                           x="0" y="0" width="650" height="425"/>

                    <defs>
                        <pattern id="maintenance-pattern" patternUnits="userSpaceOnUse" width="10" height="10" patternTransform="rotate(45)">
                            <rect width="5" height="10" fill="#1a1600"/>
                            <rect x="5" width="5" height="10" fill="#c9a227"/>
                        </pattern>
                    </defs>

                    <?php foreach ($units as $u):
                        if ($u['map_x'] === null) continue;

                        $maintenance = $u['status'] === 'maintenance';
                        $available   = (bool)$u['is_available'];
                        $clickable   = $session->isMember() && !$maintenance;
                        $hex         = $available ? '#4e9055' : '#C85050';
                        $fill        = $maintenance ? 'url(#maintenance-pattern)' : $hex;
                        $label       = htmlspecialchars($u['equipment_name'] . ' ' . $u['identifier']);
                    ?>
                    <g class="equip-node <?= $clickable ? 'equip-node--clickable' : 'equip-node--readonly' ?><?= $maintenance ? ' equip-node--maintenance' : '' ?><?= ($clickable && !$available) ? ' equip-node--busy' : '' ?>"
                       data-unit-id="<?= $u['id'] ?>"
                       role="<?= $clickable ? 'button' : 'img' ?>"
                       aria-label="<?= $label ?>"
                       tabindex="<?= $clickable ? '0' : '-1' ?>">
                        <rect x="<?= $u['map_x'] ?>" y="<?= $u['map_y'] ?>"
                              width="<?= $u['map_w'] ?>" height="<?= $u['map_h'] ?>"
                              rx="2" fill="<?= $fill ?>" stroke="<?= $maintenance ? '#c9a227' : $hex ?>" class="equip-tint"/>
                    </g>
                    <?php endforeach; ?>

                </svg>
            </div>

            <p class="map-hint">Click on a highlighted machine to reserve it.</p>
        </div>
    </main>

    <?php include '../components/footer.php'; ?>

    <?php if ($session->isMember()): ?>
    <div class="modal-backdrop" id="page-backdrop"></div>
    <dialog id="reserve-modal" class="auth-modal reserve-modal">
        <button type="button" class="btn-ghost auth-modal__close" id="reserve-close">&times;</button>
        <h2 class="auth-modal__title" id="reserve-equipment-name"></h2>
        <form method="POST" action="../actions/action_reserve_equipment.php" class="auth-modal__form">
            <input type="hidden" name="csrf_token" value="<?= $session->getCsrfToken() ?>">
            <input type="hidden" name="unit_id"    id="reserve-unit-id">
            <input type="hidden" name="start_time" id="reserve-start">
            <input type="hidden" name="end_time"   id="reserve-end">
            <input type="hidden" name="date"       id="reserve-date-hidden">

            <label class="reserve-date-label" for="reserve-date">
                <span>Date</span>
                <input type="date" id="reserve-date" name="date_display"
                       min="<?= date('Y-m-d') ?>"
                       max="<?= date('Y-m-d', strtotime('+30 days')) ?>"
                       value="<?= date('Y-m-d') ?>" required>
            </label>

            <p class="reserve-grid-label">Drag to select a time range</p>
            <div class="reserve-cal" id="reserve-cal">
                <div class="reserve-cal__gutter" id="reserve-gutter"></div>
                <div class="reserve-cal__col" id="reserve-col"></div>
            </div>
            <p class="reserve-grid-hint" id="reserve-selection-label"></p>

            <p class="auth-modal__error" id="reserve-error"></p>
            <button type="submit" class="btn-primary reserve-submit" id="reserve-submit" disabled>Confirm Reservation</button>
        </form>
    </dialog>

    <?php endif; ?>

    <script>
        const UNIT_MAP = <?= json_encode($unitMap) ?>;
    </script>
    <script src="../scripts/equipment-map.js"></script>
</body>
</html>
