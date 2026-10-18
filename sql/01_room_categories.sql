-- Table: room_categories
-- Tiano Hotel & Suites room tier specifications
CREATE TABLE IF NOT EXISTS `room_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `tagline` VARCHAR(255) DEFAULT NULL,
  `price_per_night` DECIMAL(10,2) NOT NULL,
  `capacity_adults` INT DEFAULT 2,
  `capacity_children` INT DEFAULT 1,
  `bed_type` VARCHAR(100) DEFAULT 'King Bed',
  `room_size` VARCHAR(50) DEFAULT '35 sqm',
  `description` TEXT DEFAULT NULL,
  `amenities` TEXT DEFAULT NULL,
  `featured_image` VARCHAR(255) DEFAULT 'assets/img/room/room-img1.jpg',
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initial Room Categories Seed
INSERT INTO `room_categories` (`id`, `name`, `slug`, `tagline`, `price_per_night`, `capacity_adults`, `capacity_children`, `bed_type`, `room_size`, `description`, `amenities`, `featured_image`, `status`) VALUES
(1, 'Coral Room', 'coral-room', 'Modern Comfort for Solo Guests & Business Executives', 35000.00, 2, 1, 'Queen Bed', '28 sqm', 'Designed with contemporary elegance, the Coral Room provides the perfect sanctuary for business travelers and individuals visiting Eket. Features plush bedding, a dedicated ergonomic workspace, fast Wi-Fi, and 24/7 room service.', 'Free High-Speed Wi-Fi, 43" Smart TV with DSTV, Individual Climate Control, Complimentary Gourmet Breakfast, Mini Refrigerator, En-Suite Walk-In Shower, Work Desk, 24/7 Room Service', 'assets/img/room/room-img1.jpg', 'active'),
(2, 'Gold Room', 'gold-room', 'Spacious Haven with Elevated Amenities & City Views', 48000.00, 2, 1, 'King Bed', '35 sqm', 'The Gold Room blends spacious comfort with tasteful decor. Ideal for couples or extended corporate stays, featuring premium luxury bedding, an expansive lounge chair, and full access to recreational amenities.', 'High-Speed Fiber Wi-Fi, 50" Smart TV with DSTV, Whisper-Quiet Air Conditioning, Daily Gourmet Breakfast, Coffee & Tea Station, Executive Lounge Chair, Rain Shower, Dedicated Concierge Support', 'assets/img/room/room-img2.jpg', 'active'),
(3, 'Diamond Room', 'diamond-room', 'Opulent Sophistication with Expansive Living Comfort', 65000.00, 2, 2, 'California King Bed', '45 sqm', 'Step into refined luxury with the Diamond Room. Boasting premium hardwood furnishings, a deep-soaking bathtub, floor-to-ceiling windows, and generous space to unwind in absolute privacy.', 'High-Speed Fiber Wi-Fi, 55" 4K Smart TV, Climate Control, Deep Soaking Bathtub & Rain Shower, Complimentary Gourmet Breakfast, Stocked Refreshment Bar, Plush Bathrobes & Slippers, In-Room Safe', 'assets/img/room/room-img3.jpg', 'active'),
(4, 'Premium Room', 'premium-room', 'The Executive Standard for Distinguished Guests', 85000.00, 3, 2, 'Super King Bed', '55 sqm', 'The Premium Room is engineered for guests who demand unparalleled comfort and distinction. Featuring a private sitting area, dedicated workstation, panoramic views, and priority concierge services.', 'High-Speed Fiber Wi-Fi, 65" 4K Smart TV, Separate Sitting Lounge, Luxury En-Suite Bathroom, Gourmet Breakfast for Two, Welcome Fruit Basket, 24/7 Priority Concierge, Secured Parking with Escort Service', 'assets/img/room/room-img4.jpg', 'active'),
(5, 'Presidential Suite', 'presidentalsuite', 'The Pinnacle of Aristocratic Luxury & Grandeur', 150000.00, 4, 2, 'Imperial King Bed', '90 sqm', 'The crown jewel of Tiano Hotel and Suites. Designed for VIP dignitaries, corporate heads, and special celebrations. Includes an expansive private master bedroom, opulent living room, dining area, private butler service, and unrivaled security privacy.', 'Ultra-Fast Dedicated Wi-Fi, 75" Master 4K TV, Master Suite with Jacuzzi & Steam Shower, Private Living Room & Dining Area, Dedicated Personal Butler, Full Gourmet Breakfast Banquet, VIP Security & Chauffeur Services', 'assets/img/room/room-img5.jpg', 'active')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `price_per_night`=VALUES(`price_per_night`), `tagline`=VALUES(`tagline`);
