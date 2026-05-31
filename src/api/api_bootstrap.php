<?php
declare(strict_types=1);

require_once(__DIR__ . '/../../database/connection.db.php');

function apiJson(mixed $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function apiError(string $message, int $status = 400): never
{
    apiJson(['error' => $message], $status);
}
