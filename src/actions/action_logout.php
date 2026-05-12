<?php
declare(strict_types=1);
require_once(__DIR__ . '/action_bootstrap.php');

[$session] = requireAuthenticatedPost('/src/pages/my-account.php');

$session->logout();
header('Location: /src/pages/index.php');
exit;
