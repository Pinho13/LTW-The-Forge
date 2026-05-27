<?php
declare(strict_types=1);
require_once(__DIR__ . '/../../utils/page_bootstrap.php');
require_once(__DIR__ . '/../../database/models/Equipment.class.php');

[$session, $db] = requireAuthenticatedPage();

$reservations = $session->isMember()
    ? Equipment::getReservationsForMember($db, $session->getId())
    : [];

$equipment = Equipment::getAllWithUnits($db);
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservations - The Forge</title>
    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/layout.css">
    <link rel="stylesheet" href="../style/reservations.css">
</head>

<body>
    <?php $activePage = 'reservations'; include '../components/side-menu.php'; ?>

    <main>
        <?php include '../components/flash-messages.php'; ?>

        <header>
            <h1>Reservations</h1>
        </header>

        <div class="reservations-body">

            <!-- EQUIPMENT RESERVATIONS -->
            <section class="res-section">
                <div class="res-section__header">
                    <h2 class="res-section__title">Equipment</h2>
                    <a href="/src/pages/equipment-map.php" class="btn-primary btn-sm res-section__cta">+ Reserve Equipment</a>
                </div>

                <?php
                $equipReservations = array_filter($reservations, fn($r) => true); // all are equipment for now
                if (empty($equipReservations)):
                ?>
                    <p class="res-empty">No equipment reservations yet. <a href="/src/pages/equipment-map.php">Browse equipment</a>.</p>
                <?php else: ?>
                    <div class="enrollment-sort" id="res-sort">
                        <span class="enrollment-sort__label">Sort by:</span>
                        <button type="button" class="enrollment-sort__btn enrollment-sort__btn--active" data-sort="date">Date</button>
                        <button type="button" class="enrollment-sort__btn" data-sort="name">Name</button>
                    </div>
                    <ul class="res-list" id="res-list">
                        <?php foreach ($equipReservations as $r):
                            $start    = new DateTimeImmutable($r['start_datetime']);
                            $end      = new DateTimeImmutable($r['end_datetime']);
                            $today    = (new DateTimeImmutable('today'))->format('Y-m-d');
                            $isToday  = $start->format('Y-m-d') === $today;
                            $dateStr  = $isToday
                                ? 'Today · ' . $start->format('H:i') . ' – ' . $end->format('H:i')
                                : $start->format('D j M') . ' · ' . $start->format('H:i') . ' – ' . $end->format('H:i');
                        ?>
                        <li class="res-item" data-reservation-id="<?= (int)$r['id'] ?>"
                            data-name="<?= htmlspecialchars($r['equipment_name']) ?>"
                            data-date="<?= htmlspecialchars($r['start_datetime']) ?>">
                            <div class="res-item__icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                                </svg>
                            </div>
                            <div class="res-item__info">
                                <strong class="res-item__name">
                                    <?= htmlspecialchars($r['equipment_name']) ?>
                                    <?php if ($r['identifier']): ?>
                                        <?= htmlspecialchars($r['identifier']) ?>
                                    <?php endif; ?>
                                </strong>
                                <p class="res-item__time"><?= htmlspecialchars($dateStr) ?></p>
                                <p class="res-item__sub"><?= htmlspecialchars($r['equipment_type'] ?? '') ?></p>
                            </div>
                            <form method="POST" action="../actions/action_cancel_reservation.php" class="res-item__action">
                                <input type="hidden" name="csrf_token" value="<?= $session->getCsrfToken() ?>">
                                <input type="hidden" name="reservation_id" value="<?= (int)$r['id'] ?>">
                                <button type="submit" class="btn-outline btn-sm">Cancel</button>
                            </form>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

        </div>
    </main>

    <?php include '../components/footer.php'; ?>

    <script>
        const CSRF_TOKEN = <?= json_encode($session->getCsrfToken()) ?>;
    </script>
    <script src="../scripts/reservations.js"></script>
</body>
</html>
