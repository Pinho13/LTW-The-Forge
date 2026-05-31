<?php
declare(strict_types=1);
date_default_timezone_set('Europe/Lisbon');
require_once(__DIR__ . '/../utils/deny_direct_access.php');
require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../../database/connection.db.php');
require_once(__DIR__ . '/../../database/models/User.class.php');
require_once(__DIR__ . '/../../database/models/MemberSubscription.class.php');

/**
 * Initialise session, enforce login and POST+CSRF, return [$session, $db].
 * Call at the top of every authenticated POST action.
 */
function requireAuthenticatedPost(string $redirect): array
{
    $session = new Session();
    $session->requireLogin($redirect);
    if ($_SERVER['REQUEST_METHOD'] !== 'POST'
        || !$session->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        header("Location: $redirect");
        exit;
    }
    return [$session, getDatabaseConnection()];
}

function requireAuthenticatedJsonPost(): array
{
    header('Content-Type: application/json');
    $session = new Session();
    if (!$session->isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST'
        || !$session->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid request']);
        exit;
    }
    return [$session, getDatabaseConnection()];
}
