<?php
declare(strict_types=1);
date_default_timezone_set('Europe/Lisbon');
require_once(__DIR__ . '/deny_direct_access.php');
require_once(__DIR__ . '/session.php');
require_once(__DIR__ . '/../database/connection.db.php');


function requireAuthenticatedPage(string $redirect = '/src/pages/index.php?open=login'): array
{
    $session = new Session();
    $session->requireLogin($redirect);
    return [$session, getDatabaseConnection()];
}
