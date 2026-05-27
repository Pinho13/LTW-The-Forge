<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');
require_once(__DIR__ . '/../../database/models/Equipment.class.php');

[$session, $db] = requireAuthenticatedJsonPost();

$reservationId = (int) ($_POST['reservation_id'] ?? 0);
if ($reservationId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid reservation.']);
    exit;
}

$cancelled = Equipment::cancelReservation($db, $reservationId, $session->getId());
if (!$cancelled) {
    http_response_code(404);
    echo json_encode(['error' => 'Could not cancel reservation.']);
    exit;
}

echo json_encode(['success' => true]);
