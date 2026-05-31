<?php
declare(strict_types=1);
require_once(__DIR__ . '/api_bootstrap.php');
require_once(__DIR__ . '/../../database/models/TrainerProfile.class.php');

$db = getDatabaseConnection();

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

if ($id !== null) {
    $trainer = TrainerProfile::getByUserId($db, $id);
    if ($trainer === null) {
        apiError('Trainer not found.', 404);
    }

    $upcoming = TrainerProfile::getUpcomingClasses($db, $id, 10);

    apiJson([
        'id'              => (int) $trainer['user_id'],
        'name'            => $trainer['name'],
        'username'        => $trainer['username'],
        'bio'             => $trainer['bio'] ?? null,
        'specializations' => array_values(array_filter(array_map('trim', explode(',', $trainer['specializations'] ?? '')))),
        'certifications'  => array_values(array_filter(array_map('trim', explode(',', $trainer['certifications'] ?? '')))),
        'upcoming_classes' => array_map(fn($c) => [
            'session_id' => (int) $c['session_id'],
            'class_name' => $c['class_name'],
            'type'       => $c['type_name'],
            'datetime'   => $c['datetime'],
            'room'       => $c['room'],
            'capacity'   => (int) $c['capacity'],
            'enrolled'   => (int) $c['enrolled_count'],
        ], $upcoming),
    ]);
}

$trainers = TrainerProfile::getAllWithUser($db);

apiJson([
    'count'    => count($trainers),
    'trainers' => array_map(fn($t) => [
        'id'              => (int) $t['user_id'],
        'name'            => $t['name'],
        'username'        => $t['username'],
        'bio'             => $t['bio'] ?? null,
        'specializations' => array_values(array_filter(array_map('trim', explode(',', $t['specializations'] ?? '')))),
        'certifications'  => array_values(array_filter(array_map('trim', explode(',', $t['certifications'] ?? '')))),
    ], $trainers),
]);
