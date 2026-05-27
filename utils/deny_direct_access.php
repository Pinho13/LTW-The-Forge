<?php
// Block direct HTTP requests to files that should never be served directly.
// The PHP built-in server does not honour .htaccess, so we guard here too.
if (php_sapi_name() !== 'cli' && isset($_SERVER['REQUEST_URI'])) {
    $uri = $_SERVER['REQUEST_URI'];
    if (preg_match('#^/database/#i', $uri)) {
        http_response_code(403);
        exit;
    }
}
