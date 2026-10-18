<?php
/**
 * Tiano Hotel - Quick DB Verification & Bookings Table Setup
 * Run once from browser: http://localhost/Tiano-Hotel/controllers/ensure_bookings_table.php
 * DELETE after confirming setup is complete.
 */
require_once __DIR__ . '/../include/db.php';

$output = [];

try {
    // Check if bookings table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'bookings'");
    $exists = $stmt->rowCount() > 0;
    $output[] = "bookings table exists: " . ($exists ? "YES ✓" : "NO - creating...");

    if (!$exists) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `bookings` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `booking_reference` VARCHAR(30) NOT NULL UNIQUE,
              `category_id` INT NOT NULL,
              `assigned_room_id` INT DEFAULT NULL,
              `guest_name` VARCHAR(150) NOT NULL,
              `guest_email` VARCHAR(150) NOT NULL,
              `guest_phone` VARCHAR(50) NOT NULL,
              `id_type` VARCHAR(50) DEFAULT NULL,
              `id_number` VARCHAR(100) DEFAULT NULL,
              `check_in_date` DATE NOT NULL,
              `check_out_date` DATE NOT NULL,
              `adults` INT DEFAULT 1,
              `children` INT DEFAULT 0,
              `nights` INT NOT NULL DEFAULT 1,
              `nightly_rate` DECIMAL(10,2) NOT NULL,
              `total_amount` DECIMAL(10,2) NOT NULL,
              `caution_deposit` DECIMAL(10,2) DEFAULT 0.00,
              `payment_method` ENUM('pay_on_arrival','pos','cash','bank_transfer') DEFAULT 'pay_on_arrival',
              `payment_status` ENUM('unpaid','partially_paid','paid','refunded') DEFAULT 'unpaid',
              `booking_status` ENUM('pending','confirmed','checked_in','checked_out','cancelled','no_show') DEFAULT 'confirmed',
              `special_requests` TEXT DEFAULT NULL,
              `admin_notes` TEXT DEFAULT NULL,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        $output[] = "bookings table CREATED ✓";
    }

    // Verify room_categories has data
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM room_categories");
    $row = $stmt->fetch();
    $output[] = "room_categories rows: " . $row['cnt'];

    // List existing bookings count
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM bookings");
    $row = $stmt->fetch();
    $output[] = "bookings rows: " . $row['cnt'];

    // List site_settings
    $stmt = $pdo->query("SHOW TABLES LIKE 'site_settings'");
    $output[] = "site_settings table exists: " . ($stmt->rowCount() > 0 ? "YES ✓" : "NO ✗");

    echo "<pre style='font-family:monospace;font-size:14px;padding:20px;'>";
    echo "=== Tiano Hotel DB Verification ===\n\n";
    foreach ($output as $line) {
        echo $line . "\n";
    }
    echo "\n=== ALL OK ===\n";
    echo "</pre>";

} catch (Exception $e) {
    echo "<pre style='color:red;'>ERROR: " . $e->getMessage() . "</pre>";
}
