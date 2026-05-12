<?php
declare(strict_types=1);
require_once(__DIR__ . '/../../utils/session.php');
require_once(__DIR__ . '/../../database/connection.db.php');
require_once(__DIR__ . '/../../database/User.class.php');
require_once(__DIR__ . '/../../database/MemberSubscription.class.php');

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
