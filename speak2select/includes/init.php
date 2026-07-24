<?php
/*
 Speak2Select™ – Application Bootstrap
 Author: Praveen Kumar Kotha
*/

define('APP_ACCESS', true);

session_start();

/* =========================
   LOAD .env FIRST
   ========================= */
$envPath = dirname(__DIR__) . '/.env';

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);

        if (!defined($key)) {
            define($key, trim($value));
        }
    }
}

/* =========================
   LOAD DATABASE AFTER ENV
   ========================= */
require_once __DIR__ . '/db.php';
