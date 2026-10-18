-- Table: gallery
-- Photo showcase images with category filtering, optimized thumbnail and broad-view master paths
CREATE TABLE IF NOT EXISTS `gallery` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `category_filter` VARCHAR(50) NOT NULL DEFAULT 'rooms',
  `thumb_url` VARCHAR(255) NOT NULL,
  `full_url` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initial Gallery Items Seed Data
INSERT INTO `gallery` (`id`, `title`, `category_filter`, `thumb_url`, `full_url`, `caption`, `sort_order`, `status`) VALUES
(1, 'Executive Suite Bedroom', 'rooms', 'assets/img/gallery/room-img35.jpg', 'assets/img/gallery/room-img35.jpg', 'Modern King Bed with luxurious ambient lighting', 1, 'active'),
(2, 'Deluxe Room Living Space', 'rooms', 'assets/img/gallery/room-img36.jpg', 'assets/img/gallery/room-img36.jpg', 'Comfortable lounge area in our deluxe executive room', 2, 'active'),
(3, 'Hotel Architectural Panorama', 'exterior', 'assets/img/gallery/7.png', 'assets/img/gallery/7.png', 'Serene premises and architecture at 44 Marina Road', 3, 'active'),
(4, 'Recreational Poolside Terrace', 'facilities', 'assets/img/gallery/18.png', 'assets/img/gallery/18.png', 'Crystal clear swimming pool for refreshing leisure', 4, 'active'),
(5, 'Evening Ambient Pool Deck', 'facilities', 'assets/img/gallery/19.png', 'assets/img/gallery/19.png', 'Warm nighttime illumination across the pool terrace', 5, 'active'),
(6, 'VIP Lounge & Dining', 'dining', 'assets/img/gallery/18-1.png', 'assets/img/gallery/18-1.png', 'Intimate indoor dining with gourmet chef preparations', 6, 'active'),
(7, 'Bush Bar & Open-Air Cabana', 'dining', 'assets/img/gallery/27.png', 'assets/img/gallery/27.png', 'Signature bush bar with fresh grills and cold refreshments', 7, 'active'),
(8, 'Conference & Event Banqueting', 'events', 'assets/img/gallery/28.png', 'assets/img/gallery/28.png', 'Versatile banquet hall setup for corporate meetings', 8, 'active')
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`), `thumb_url`=VALUES(`thumb_url`);
