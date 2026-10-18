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

-- Official Hotel Management & Staff Accounts
INSERT INTO `admin_users` (`id`, `username`, `fullname`, `email`, `password_hash`, `role`, `is_verified`, `status`) VALUES
(1, 'admin', 'Tiano Hotel Administrator', 'support@tianohotelandsuites.com', '$2y$10$KYHjSGeEOvzKGoQ10bYAGOmpflZJnJEq8yB0MZTjeGpavjXgeY0IW', 'super_admin', 1, 'active'),
(2, 'dev', 'Weburea Dev Ops', 'dev@tianohotelandsuites.com', '$2y$10$F46UCRKxM7maLKqVsWUUAusCn/fIMhM80Vd8I9rQC49ZxBMW2GpUe', 'super_admin', 1, 'active'),
(3, 'manager', 'Tiano General Manager', 'management@tianohotelandsuites.com', '$2y$10$dyVECavZVevoPzkpnsSVO.r0H/NwE2oVYQHYezncNOn7DaOOBXw6u', 'manager', 1, 'active'),
(4, 'reception', 'Tiano Front Desk Reception', 'frontdesk@tianohotelandsuites.com', '$2y$10$wV8qrI3Zm/fQD/xrf5xp1.gcvtfjqMUShBxuobj.alPPrI6pFJMyi', 'receptionist', 1, 'active')
ON DUPLICATE KEY UPDATE `fullname`=VALUES(`fullname`), `password_hash`=VALUES(`password_hash`), `role`=VALUES(`role`), `is_verified`=VALUES(`is_verified`), `status`=VALUES(`status`);


