-- Table: bookings
-- Guest reservations, booking reference vouchers, check-in/out folios and payment on arrival tracking
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
  `payment_method` ENUM('pay_on_arrival', 'pos', 'cash', 'bank_transfer') DEFAULT 'pay_on_arrival',
  `payment_status` ENUM('unpaid', 'partially_paid', 'paid', 'refunded') DEFAULT 'unpaid',
  `booking_status` ENUM('pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show') DEFAULT 'confirmed',
  `special_requests` TEXT DEFAULT NULL,
  `admin_notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_bookings_category` FOREIGN KEY (`category_id`) REFERENCES `room_categories` (`id`),
  CONSTRAINT `fk_bookings_assigned_room` FOREIGN KEY (`assigned_room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample initial test booking to verify vouchers and receptionist view
INSERT INTO `bookings` (`id`, `booking_reference`, `category_id`, `assigned_room_id`, `guest_name`, `guest_email`, `guest_phone`, `id_type`, `id_number`, `check_in_date`, `check_out_date`, `adults`, `children`, `nights`, `nightly_rate`, `total_amount`, `caution_deposit`, `payment_method`, `payment_status`, `booking_status`, `special_requests`, `admin_notes`) VALUES
(1, 'THS-782410', 3, 9, 'Engr. Emem Akpan', 'emem.akpan@example.com', '+234 803 123 4567', 'Driver\'s License', 'AKW-98214-AA', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 2 DAY), 2, 0, 2, 65000.00, 130000.00, 10000.00, 'pay_on_arrival', 'unpaid', 'confirmed', 'Arriving in the evening. Quiet room requested.', 'Verified guest identity via phone. Holding room 301 until 6:00 PM.')
ON DUPLICATE KEY UPDATE `booking_reference`=VALUES(`booking_reference`);
