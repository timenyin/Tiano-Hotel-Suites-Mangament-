-- Table: services
-- Hotel services and amenities directory
CREATE TABLE IF NOT EXISTS `services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `icon_class` VARCHAR(100) DEFAULT 'flaticon-hotel',
  `short_description` VARCHAR(255) DEFAULT NULL,
  `full_content` TEXT DEFAULT NULL,
  `featured_image` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initial Services Seed Data
INSERT INTO `services` (`id`, `title`, `slug`, `icon_class`, `short_description`, `full_content`, `featured_image`, `sort_order`, `status`) VALUES
(1, 'Luxurious & Comfortable Accommodation', 'luxurious_comfortable_accommodation', 'flaticon-hotel', 'Enjoy a refined stay across our 5 distinguished tiers: Coral, Gold, Diamond, Premium, and Presidential Suite.', 'Every room at Tiano Hotel and Suites is designed to provide supreme relaxation, featuring plush bedding, en-suite baths, fast Wi-Fi, and 24/7 dedicated service in the heart of Eket.', 'assets/img/services/service-details-img1.jpg', 1, 'active'),
(2, 'Complimentary Gourmet Breakfast', 'complimentary_gourmet_breakfast', 'flaticon-restaurant', 'Start your morning with freshly prepared culinary delights, local specialties, and continental favorites.', 'Served daily between 6:30 AM and 10:00 AM, our complimentary breakfast combines fresh farm-to-table ingredients with exquisite continental and traditional Akwa Ibom flavors.', 'assets/img/services/service-details-img2.jpg', 2, 'active'),
(3, 'Modern Wellness, Spa & Recreation', 'modern_wellness_recreation_facilities', 'flaticon-resort', 'Relax and rejuvenate with our sparkling outdoor pool, gym, spa therapy, and poolside bush bar.', 'Recharge your senses. Guests enjoy full access to our swimming pool terrace, modern fitness facilities, therapeutic massage packages, and evening leisure at the open-air bush bar.', 'assets/img/services/service-details-img3.jpg', 3, 'active'),
(4, 'Secured & Serene Environment', 'secured_environment', 'flaticon-buildings', 'Round-the-clock trained security personnel, monitored perimeter CCTV, and access control.', 'Your safety and peace of mind are paramount. Tiano Hotel operates a closed secured perimeter with 24-hour manned checkpoints, private parking surveillance, and corporate escort coordination.', 'assets/img/services/service-details-img4.jpg', 4, 'active')
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`), `short_description`=VALUES(`short_description`);
