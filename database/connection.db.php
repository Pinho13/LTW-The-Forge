<?php
declare(strict_types=1);

function getDatabaseConnection(): PDO
{
    $databasePath = __DIR__ . '/sql/database.db';
    $dsn = 'sqlite:' . $databasePath;

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    $database = new PDO($dsn, null, null, $options);
    $database->exec('PRAGMA foreign_keys = ON');

    return $database;
}