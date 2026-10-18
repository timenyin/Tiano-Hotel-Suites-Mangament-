-- Table: admin_users
-- Hotel management credentials, staff authentication, email verification and password recovery
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `fullname` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin', 'manager', 'receptionist') DEFAULT 'receptionist',
  `is_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `verification_token` VARCHAR(100) DEFAULT NULL,
  `verification_code` VARCHAR(10) DEFAULT NULL,
  `verification_expires` DATETIME DEFAULT NULL,
  `reset_token` VARCHAR(100) DEFAULT NULL,
  `reset_code` VARCHAR(10) DEFAULT NULL,
  `reset_expires` DATETIME DEFAULT NULL,
  `last_login` DATETIME DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initial Administrator Account (Default Credentials: admin / Admin@Tiano2026)
INSERT INTO `admin_users` (`id`, `username`, `fullname`, `email`, `password_hash`, `role`, `is_verified`, `status`) VALUES
(1, 'admin', 'Tiano Hotel Administrator', 'support@tianohotelandsuites.com', '$2y$10$KYHjSGeEOvzKGoQ10bYAGOmpflZJnJEq8yB0MZTjeGpavjXgeY0IW', 'super_admin', 1, 'active')
ON DUPLICATE KEY UPDATE `email`=VALUES(`email`), `password_hash`=VALUES(`password_hash`), `is_verified`=VALUES(`is_verified`);

