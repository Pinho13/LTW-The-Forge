<?php
declare(strict_types=1);

// Calendar grid constants and functions shared between classes and trainer-schedule pages
const GRID_START_HOUR = 8;
const GRID_ROWS       = 52; // 13 hours * 4 rows per hour (15-min slots)

function timeToGridRow(string $datetime): int {
    $dt     = new DateTimeImmutable($datetime);
    $hour   = (int) $dt->format('G');
    $min    = (int) $dt->format('i');
    $offset = ($hour - GRID_START_HOUR) * 4 + intdiv($min, 15);
    return max(1, $offset + 1);
}

function durationToGridSpan(int $minutes): int {
    return max(1, intdiv($minutes, 15));
}
