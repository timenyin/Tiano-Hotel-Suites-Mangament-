<?php
/**
 * Database Connection Configuration for Tiano Hotel and Suites
 * Automatically detects whether running locally (XAMPP) or on live cPanel hosting.
 */

$is_local_env = (
    (isset($_SERVER['SERVER_NAME']) && in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1'])) ||
    (isset($_SERVER['HTTP_HOST']) && in_array(parse_url('http://' . $_SERVER['HTTP_HOST'], PHP_URL_HOST), ['localhost', '127.0.0.1'])) ||
    (php_sapi_name() === 'cli' && (stripos(__DIR__, 'xampp') !== false || stripos(__DIR__, 'c:\\') === 0))
);

if ($is_local_env) {
    // 1. LOCALHOST SETTINGS (Active for Local Development)
    $host    = 'localhost';
    $db      = 'tiano_hotel_db';
    $user    = 'root';
    $pass    = '';
    $charset = 'utf8mb4';
} else {
    // 2. ONLINE HOSTING SETTINGS (Active for Live Deployment)
    $host    = 'localhost';
    $db      = 'tianohot_hotel_db';
    $user    = 'tianohot_hotel_user';
    $pass    = 'AWjI65%&gC*(p5xk';
    $charset = 'utf8mb4';
}