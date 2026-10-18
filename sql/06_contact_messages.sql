-- Table: contact_messages
-- Inbound inquiries submitted from the public contact page
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone_number` VARCHAR(50) DEFAULT NULL,
  `msg_subject` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `admin_reply` TEXT DEFAULT NULL,
  `replied_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample contact message
INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone_number`, `msg_subject`, `message`, `is_read`) VALUES
(1, 'Barrister Anietie Udo', 'anietie.udo@lexcorp.ng', '+234 802 987 6543', 'Corporate Conference Hall Inquiry', 'Good day, we are planning a 2-day corporate legal retreat in Eket next month. Please share details regarding conference room capacity, catering packages, and block room rates.', 0)
ON DUPLICATE KEY UPDATE `email`=VALUES(`email`);
