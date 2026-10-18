-- Table: inquiries
-- Corporate contracts, extended long-stay proposals, event hall & bush bar bookings
CREATE TABLE IF NOT EXISTS `inquiries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `company_name` VARCHAR(200) NOT NULL,
  `contact_person` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `inquiry_type` ENUM('long_stay', 'event_hall', 'bush_bar', 'catering', 'other') DEFAULT 'long_stay',
  `start_date` DATE DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `estimated_guests` INT DEFAULT 1,
  `details` TEXT NOT NULL,
  `status` ENUM('new', 'in_progress', 'quoted', 'closed') DEFAULT 'new',
  `admin_notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample initial corporate inquiry
INSERT INTO `inquiries` (`id`, `company_name`, `contact_person`, `email`, `phone`, `inquiry_type`, `start_date`, `end_date`, `estimated_guests`, `details`, `status`, `admin_notes`) VALUES
(1, 'ExxonMobil Contractor Services', 'Mr. David Mensah', 'd.mensah@contractor-logistics.com', '+234 803 555 0192', 'long_stay', DATE_ADD(CURDATE(), INTERVAL 14 DAY), DATE_ADD(CURDATE(), INTERVAL 45 DAY), 6, 'Requesting corporate pricing for 6 Diamond & Gold rooms for an offshore technical team on a 30-day rotation in Eket. Requires daily laundry, breakfast, and dedicated vehicle parking.', 'new', 'Contacted team via email; preparing customized corporate tariff proposal.')
ON DUPLICATE KEY UPDATE `company_name`=VALUES(`company_name`);
