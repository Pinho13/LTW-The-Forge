<?php
declare(strict_types=1);
require_once(__DIR__ . '/../../utils/page_bootstrap.php');
require_once(__DIR__ . '/../../database/models/Equipment.class.php');

[$session, $db] = requireAuthenticatedPage();

$units    = Equipment::getAllUnitsWithStatus($db);
$catalog  = Equipment::getCatalog($db);
$isAdmin  = $session->isAdmin();

$unitMap = [];
foreach ($units as $u) {
    $unitMap[(int)$u['id']] = [
        'id'             => (int)$u['id'],
        'equipment_id'   => (int)$u['equipment_id'],
        'identifier'     => $u['identifier'],
        'equipment_name' => $u['equipment_name'],
        'photo'          => $u['photo'] ?? '',
        'status'         => $u['status'],
        'is_available'   => (bool)$u['is_available'],
        'rotation'       => (int)($u['rotation'] ?? 0),
        'map_x'          => $u['map_x'] !== null ? (int)$u['map_x'] : null,
        'map_y'          => $u['map_y'] !== null ? (int)$u['map_y'] : null,
        'map_w'          => $u['map_w'] !== null ? (int)$u['map_w'] : null,
        'map_h'          => $u['map_h'] !== null ? (int)$u['map_h'] : null,
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
    <?php if ($isAdmin): ?>
    <link rel="stylesheet" href="../style/equipment-map-admin.css">
    <?php endif; ?>
</head>

<body>
    <?php $activePage = 'equipment'; include '../components/side-menu.php'; ?>

    <main>
        <?php include '../components/flash-messages.php'; ?>

        <header>
            <h1>Equipment</h1>
            <?php if ($isAdmin): ?>
            <div class="equip-header-actions">
                <button type="button" id="edit-layout-btn" class="btn-ghost equip-admin-btn">Edit Layout</button>
                <button type="button" id="edit-status-btn" class="btn-ghost equip-admin-btn">Edit Status</button>
            </div>
            <?php endif; ?>
        </header>

        <?php if ($isAdmin): ?>
        <div class="edit-toolbar" id="edit-toolbar" hidden>
            <div class="edit-toolbar__left">
                <span class="edit-toolbar__label">Drag equipment onto the map. Click a placed unit to rotate or remove it.</span>
            </div>
            <div class="edit-toolbar__right">
                <button type="button" id="edit-save-btn" class="edit-save-btn">Save Layout</button>
            </div>
        </div>
        <div class="edit-toolbar" id="status-toolbar" hidden>
            <div class="edit-toolbar__left">
                <span class="edit-toolbar__label">Click a unit on the map to toggle between available and maintenance.</span>
            </div>
            <div class="edit-toolbar__right">
                <button type="button" id="status-done-btn" class="edit-save-btn">Save Status</button>
            </div>
        </div>
        <?php endif; ?>

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
                        $label       = htmlspecialchars($u['equipment_name'] . ' ' . $u['identifier']);
                        $rotation    = (int)($u['rotation'] ?? 0);
                        $mx          = (int)$u['map_x'];
                        $my          = (int)$u['map_y'];
                        $mw          = (int)$u['map_w'];
                        $mh          = (int)$u['map_h'];
                        $cx          = $mx + $mw / 2;
                        $cy          = $my + $mh / 2;
                        $imgTransform = $rotation !== 0 ? "rotate($rotation $cx $cy)" : '';
                        // Axis-aligned bounding box for the hit rect
                        $swap        = $rotation === 90 || $rotation === 270;
                        $bbW         = $swap ? $mh : $mw;
                        $bbH         = $swap ? $mw : $mh;
                        $bbX         = $cx - $bbW / 2;
                        $bbY         = $cy - $bbH / 2;
                        $photoSrc    = '/database/assets/equipment/' . htmlspecialchars($u['photo'] ?? '');
                    ?>
                    <g class="equip-node <?= $clickable ? 'equip-node--clickable' : 'equip-node--readonly' ?><?= $maintenance ? ' equip-node--maintenance' : '' ?><?= ($clickable && !$available) ? ' equip-node--busy' : '' ?>"
                       data-unit-id="<?= $u['id'] ?>"
                       role="<?= $clickable ? 'button' : 'img' ?>"
                       aria-label="<?= $label ?>"
                       tabindex="<?= $clickable ? '0' : '-1' ?>">
                        <?php if ($u['photo']): ?>
                        <g <?= $imgTransform ? "transform=\"$imgTransform\"" : '' ?>>
                            <image href="<?= $photoSrc ?>"
                                   x="<?= $mx ?>" y="<?= $my ?>"
                                   width="<?= $mw ?>" height="<?= $mh ?>"
                                   preserveAspectRatio="xMidYMid meet"
                                   class="equip-img"/>
                        </g>
                        <?php endif; ?>
                        <rect x="<?= $bbX ?>" y="<?= $bbY ?>"
                              width="<?= $bbW ?>" height="<?= $bbH ?>"
                              rx="2" class="equip-tint"
                              <?php if ($maintenance): ?>
                              fill="url(#maintenance-pattern)" stroke="#c9a227"
                              <?php elseif (!$available): ?>
                              fill="#C85050" stroke="#C85050"
                              <?php else: ?>
                              fill="#4e9055" stroke="#4e9055"
                              <?php endif; ?>
                        />
                    </g>
                    <?php endforeach; ?>

                </svg>
            </div>

            <p class="map-hint" id="map-hint">Click on a highlighted machine to reserve it.</p>
        </div>

        <?php if ($isAdmin): ?>
        <div class="picker-panel" id="picker-panel" hidden>
            <p class="picker-panel__title">Add Equipment</p>
            <div class="picker-grid" id="picker-grid">
                <?php foreach ($catalog as $eq): ?>
                <div class="picker-item"
                     draggable="true"
                     data-eq-id="<?= (int)$eq['id'] ?>"
                     data-eq-name="<?= htmlspecialchars($eq['name']) ?>"
                     data-eq-photo="<?= htmlspecialchars($eq['photo'] ?? '') ?>"
                     data-eq-w="<?= (int)$eq['default_w'] ?>"
                     data-eq-h="<?= (int)$eq['default_h'] ?>">
                    <?php if ($eq['photo']): ?>
                    <img src="/database/assets/equipment/<?= htmlspecialchars($eq['photo']) ?>"
                         alt="<?= htmlspecialchars($eq['name']) ?>" class="picker-item__img">
                    <?php endif; ?>
                    <span class="picker-item__name"><?= htmlspecialchars($eq['name']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
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
        const UNIT_MAP   = <?= json_encode($unitMap) ?>;
        const IS_ADMIN   = <?= $isAdmin ? 'true' : 'false' ?>;
        const CSRF_TOKEN = <?= json_encode($session->getCsrfToken()) ?>;
    </script>
    <script src="../scripts/equipment-map.js"></script>
    <?php if ($isAdmin): ?>
    <script src="../scripts/equipment-map-admin.js"></script>
    <?php endif; ?>
</body>
</html>
