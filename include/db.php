<?php
/**
 * Database Connection Handler
 * Uses PDO for enhanced security, prepared statements, and SQL injection prevention.
 */
require_once __DIR__ . '/pdoconfig.php';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    date_default_timezone_set('Africa/Lagos');
    $pdo->exec("SET time_zone = '+01:00'");
} catch (\PDOException $e) {
    // If the database does not exist yet during migration setup, provide a clear diagnostic
    die("Database connection failed: " . $e->getMessage());
}
?>
