-- =====================================================================
-- TIANO HOTEL AND SUITES - MASTER DATABASE SCHEMA & SEED DATA
-- Location: 44 Marina Road, Eket, Akwa Ibom State, Nigeria
-- Target Engine: MariaDB / MySQL 5.7+ / 8.0+
-- Character Set: utf8mb4 / utf8mb4_unicode_ci
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `tiano_hotel_db` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `tiano_hotel_db`;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- 1. Table: room_categories
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `room_categories`;
CREATE TABLE `room_categories` (
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

INSERT INTO `room_categories` (`id`, `name`, `slug`, `tagline`, `price_per_night`, `capacity_adults`, `capacity_children`, `bed_type`, `room_size`, `description`, `amenities`, `featured_image`, `status`) VALUES
(1, 'Coral Room', 'coral-room', 'Modern Comfort for Solo Guests & Business Executives', 35000.00, 2, 1, 'Queen Bed', '28 sqm', 'Designed with contemporary elegance, the Coral Room provides the perfect sanctuary for business travelers and individuals visiting Eket. Features plush bedding, a dedicated ergonomic workspace, fast Wi-Fi, and 24/7 room service.', 'Free High-Speed Wi-Fi, 43" Smart TV with DSTV, Individual Climate Control, Complimentary Gourmet Breakfast, Mini Refrigerator, En-Suite Walk-In Shower, Work Desk, 24/7 Room Service', 'assets/img/room/room-img1.jpg', 'active'),
(2, 'Gold Room', 'gold-room', 'Spacious Haven with Elevated Amenities & City Views', 48000.00, 2, 1, 'King Bed', '35 sqm', 'The Gold Room blends spacious comfort with tasteful decor. Ideal for couples or extended corporate stays, featuring premium luxury bedding, an expansive lounge chair, and full access to recreational amenities.', 'High-Speed Fiber Wi-Fi, 50" Smart TV with DSTV, Whisper-Quiet Air Conditioning, Daily Gourmet Breakfast, Coffee & Tea Station, Executive Lounge Chair, Rain Shower, Dedicated Concierge Support', 'assets/img/room/room-img2.jpg', 'active'),
(3, 'Diamond Room', 'diamond-room', 'Opulent Sophistication with Expansive Living Comfort', 65000.00, 2, 2, 'California King Bed', '45 sqm', 'Step into refined luxury with the Diamond Room. Boasting premium hardwood furnishings, a deep-soaking bathtub, floor-to-ceiling windows, and generous space to unwind in absolute privacy.', 'High-Speed Fiber Wi-Fi, 55" 4K Smart TV, Climate Control, Deep Soaking Bathtub & Rain Shower, Complimentary Gourmet Breakfast, Stocked Refreshment Bar, Plush Bathrobes & Slippers, In-Room Safe', 'assets/img/room/room-img3.jpg', 'active'),
(4, 'Premium Room', 'premium-room', 'The Executive Standard for Distinguished Guests', 85000.00, 3, 2, 'Super King Bed', '55 sqm', 'The Premium Room is engineered for guests who demand unparalleled comfort and distinction. Featuring a private sitting area, dedicated workstation, panoramic views, and priority concierge services.', 'High-Speed Fiber Wi-Fi, 65" 4K Smart TV, Separate Sitting Lounge, Luxury En-Suite Bathroom, Gourmet Breakfast for Two, Welcome Fruit Basket, 24/7 Priority Concierge, Secured Parking with Escort Service', 'assets/img/room/room-img4.jpg', 'active'),
(5, 'Presidential Suite', 'presidentalsuite', 'The Pinnacle of Aristocratic Luxury & Grandeur', 150000.00, 4, 2, 'Imperial King Bed', '90 sqm', 'The crown jewel of Tiano Hotel and Suites. Designed for VIP dignitaries, corporate heads, and special celebrations. Includes an expansive private master bedroom, opulent living room, dining area, private butler service, and unrivaled security privacy.', 'Ultra-Fast Dedicated Wi-Fi, 75" Master 4K TV, Master Suite with Jacuzzi & Steam Shower, Private Living Room & Dining Area, Dedicated Personal Butler, Full Gourmet Breakfast Banquet, VIP Security & Chauffeur Services', 'assets/img/room/room-img5.jpg', 'active');

-- ---------------------------------------------------------------------
-- 2. Table: rooms
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `rooms`;
CREATE TABLE `rooms` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `room_number` VARCHAR(20) NOT NULL UNIQUE,
  `floor_number` INT DEFAULT 1,
  `cleanliness_status` ENUM('clean', 'inspected', 'dirty', 'maintenance') DEFAULT 'clean',
  `occupancy_status` ENUM('vacant', 'occupied', 'reserved') DEFAULT 'vacant',
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_rooms_category` FOREIGN KEY (`category_id`) REFERENCES `room_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `rooms` (`id`, `category_id`, `room_number`, `floor_number`, `cleanliness_status`, `occupancy_status`, `notes`) VALUES
(1, 1, '101', 1, 'clean', 'vacant', 'Coral Wing - Ground floor garden view'),
(2, 1, '102', 1, 'clean', 'vacant', 'Coral Wing - Near quiet courtyard'),
(3, 1, '103', 1, 'clean', 'vacant', 'Coral Wing - Easy lobby access'),
(4, 1, '104', 1, 'inspected', 'vacant', 'Coral Wing - Double checked by housekeeping'),
(5, 2, '201', 2, 'clean', 'vacant', 'Gold Wing - 2nd floor city view'),
(6, 2, '202', 2, 'clean', 'vacant', 'Gold Wing - Quiet corner unit'),
(7, 2, '203', 2, 'clean', 'vacant', 'Gold Wing - Balcony facing Marina Road'),
(8, 2, '204', 2, 'inspected', 'vacant', 'Gold Wing - Executive layout'),
(9, 3, '301', 3, 'clean', 'vacant', 'Diamond Wing - 3rd floor panoramic vista'),
(10, 3, '302', 3, 'clean', 'vacant', 'Diamond Wing - Luxury soaking tub equipped'),
(11, 3, '303', 3, 'inspected', 'vacant', 'Diamond Wing - Premium corner room'),
(12, 4, '401', 4, 'clean', 'vacant', 'Executive Wing - 4th floor quiet suite'),
(13, 4, '402', 4, 'inspected', 'vacant', 'Executive Wing - Separate lounge parlor'),
(14, 5, 'PH-01', 5, 'inspected', 'vacant', 'Penthouse Level - Presidential Suite VIP Wing');

-- ---------------------------------------------------------------------
-- 3. Table: bookings
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `bookings`;
CREATE TABLE `bookings` (
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

INSERT INTO `bookings` (`id`, `booking_reference`, `category_id`, `assigned_room_id`, `guest_name`, `guest_email`, `guest_phone`, `id_type`, `id_number`, `check_in_date`, `check_out_date`, `adults`, `children`, `nights`, `nightly_rate`, `total_amount`, `caution_deposit`, `payment_method`, `payment_status`, `booking_status`, `special_requests`, `admin_notes`) VALUES
(1, 'THS-782410', 3, 9, 'Engr. Emem Akpan', 'emem.akpan@example.com', '+234 803 123 4567', 'Driver\'s License', 'AKW-98214-AA', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 2 DAY), 2, 0, 2, 65000.00, 130000.00, 10000.00, 'pay_on_arrival', 'unpaid', 'confirmed', 'Arriving in the evening. Quiet room requested.', 'Verified guest identity via phone. Holding room 301 until 6:00 PM.');

-- ---------------------------------------------------------------------
-- 4. Table: gallery
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `gallery`;
CREATE TABLE `gallery` (
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

INSERT INTO `gallery` (`id`, `title`, `category_filter`, `thumb_url`, `full_url`, `caption`, `sort_order`, `status`) VALUES
(1, 'Executive Suite Bedroom', 'rooms', 'assets/img/gallery/room-img35.jpg', 'assets/img/gallery/room-img35.jpg', 'Modern King Bed with luxurious ambient lighting', 1, 'active'),
(2, 'Deluxe Room Living Space', 'rooms', 'assets/img/gallery/room-img36.jpg', 'assets/img/gallery/room-img36.jpg', 'Comfortable lounge area in our deluxe executive room', 2, 'active'),
(3, 'Hotel Architectural Panorama', 'exterior', 'assets/img/gallery/7.png', 'assets/img/gallery/7.png', 'Serene premises and architecture at 44 Marina Road', 3, 'active'),
(4, 'Recreational Poolside Terrace', 'facilities', 'assets/img/gallery/18.png', 'assets/img/gallery/18.png', 'Crystal clear swimming pool for refreshing leisure', 4, 'active'),
(5, 'Evening Ambient Pool Deck', 'facilities', 'assets/img/gallery/19.png', 'assets/img/gallery/19.png', 'Warm nighttime illumination across the pool terrace', 5, 'active'),
(6, 'VIP Lounge & Dining', 'dining', 'assets/img/gallery/18-1.png', 'assets/img/gallery/18-1.png', 'Intimate indoor dining with gourmet chef preparations', 6, 'active'),
(7, 'Bush Bar & Open-Air Cabana', 'dining', 'assets/img/gallery/27.png', 'assets/img/gallery/27.png', 'Signature bush bar with fresh grills and cold refreshments', 7, 'active'),
(8, 'Conference & Event Banqueting', 'events', 'assets/img/gallery/28.png', 'assets/img/gallery/28.png', 'Versatile banquet hall setup for corporate meetings', 8, 'active');

-- ---------------------------------------------------------------------
-- 5. Table: services
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
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

INSERT INTO `services` (`id`, `title`, `slug`, `icon_class`, `short_description`, `full_content`, `featured_image`, `sort_order`, `status`) VALUES
(1, 'Luxurious & Comfortable Accommodation', 'luxurious_comfortable_accommodation', 'flaticon-hotel', 'Enjoy a refined stay across our 5 distinguished tiers: Coral, Gold, Diamond, Premium, and Presidential Suite.', 'Every room at Tiano Hotel and Suites is designed to provide supreme relaxation, featuring plush bedding, en-suite baths, fast Wi-Fi, and 24/7 dedicated service in the heart of Eket.', 'assets/img/services/service-details-img1.jpg', 1, 'active'),
(2, 'Complimentary Gourmet Breakfast', 'complimentary_gourmet_breakfast', 'flaticon-restaurant', 'Start your morning with freshly prepared culinary delights, local specialties, and continental favorites.', 'Served daily between 6:30 AM and 10:00 AM, our complimentary breakfast combines fresh farm-to-table ingredients with exquisite continental and traditional Akwa Ibom flavors.', 'assets/img/services/service-details-img2.jpg', 2, 'active'),
(3, 'Modern Wellness, Spa & Recreation', 'modern_wellness_recreation_facilities', 'flaticon-resort', 'Relax and rejuvenate with our sparkling outdoor pool, gym, spa therapy, and poolside bush bar.', 'Recharge your senses. Guests enjoy full access to our swimming pool terrace, modern fitness facilities, therapeutic massage packages, and evening leisure at the open-air bush bar.', 'assets/img/services/service-details-img3.jpg', 3, 'active'),
(4, 'Secured & Serene Environment', 'secured_environment', 'flaticon-buildings', 'Round-the-clock trained security personnel, monitored perimeter CCTV, and access control.', 'Your safety and peace of mind are paramount. Tiano Hotel operates a closed secured perimeter with 24-hour manned checkpoints, private parking surveillance, and corporate escort coordination.', 'assets/img/services/service-details-img4.jpg', 4, 'active');

-- ---------------------------------------------------------------------
-- 6. Table: contact_messages
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
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

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone_number`, `msg_subject`, `message`, `is_read`) VALUES
(1, 'Barrister Anietie Udo', 'anietie.udo@lexcorp.ng', '+234 802 987 6543', 'Corporate Conference Hall Inquiry', 'Good day, we are planning a 2-day corporate legal retreat in Eket next month. Please share details regarding conference room capacity, catering packages, and block room rates.', 0);

-- ---------------------------------------------------------------------
-- 7. Table: newsletter_subscribers
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `newsletter_subscribers`;
CREATE TABLE `newsletter_subscribers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `status` ENUM('active', 'unsubscribed') DEFAULT 'active',
  `subscribed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `newsletter_subscribers` (`id`, `email`, `status`) VALUES
(1, 'guest.relations@tianohotel.com', 'active');

-- ---------------------------------------------------------------------
-- 8. Table: faqs
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `faqs`;
CREATE TABLE `faqs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `question` VARCHAR(255) NOT NULL,
  `answer` TEXT NOT NULL,
  `category` VARCHAR(50) DEFAULT 'general',
  `sort_order` INT DEFAULT 0,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `sort_order`, `status`) VALUES
(1, 'What is the payment policy for online reservations?', 'We operate a transparent Pay-on-Arrival policy. When you make an online booking, a digital voucher is generated with your unique booking reference. You settle payment physically at our front desk upon arrival using POS terminal, cash, or verified instant bank transfer.', 'booking', 1, 'active'),
(2, 'What time is standard check-in and check-out?', 'Standard check-in commences at 1:00 PM, and check-out is by 12:00 PM (noon). Early check-in or late check-out requests are accommodated based on room availability and may attract an extended stay charge.', 'stay', 2, 'active'),
(3, 'Is complimentary breakfast included with all room categories?', 'Yes! Every room booking at Tiano Hotel and Suites includes our complimentary gourmet breakfast banquet served daily from 6:30 AM to 10:00 AM at our restaurant.', 'dining', 3, 'active'),
(4, 'How secure is the hotel premises on Marina Road, Eket?', 'We maintain 24-hour perimeter security patrols, monitored high-definition CCTV surveillance, secure access control gates, and private guarded on-site parking for all our guests.', 'security', 4, 'active'),
(5, 'Do you host corporate events and private celebrations?', 'Yes, our modern conference halls and scenic poolside bush bar are fully equipped with multimedia systems and dedicated banqueting catering for conferences, retreats, and social functions.', 'events', 5, 'active');

-- ---------------------------------------------------------------------
-- 9. Table: reviews
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `guest_name` VARCHAR(150) NOT NULL,
  `guest_location` VARCHAR(100) DEFAULT 'Nigeria',
  `guest_photo` VARCHAR(255) DEFAULT 'assets/img/testimonials/testimonials-img1.jpg',
  `rating` INT NOT NULL DEFAULT 5,
  `cleanliness_rating` INT DEFAULT 5,
  `staff_rating` INT DEFAULT 5,
  `security_rating` INT DEFAULT 5,
  `review_text` TEXT NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `reviews` (`id`, `guest_name`, `guest_location`, `guest_photo`, `rating`, `cleanliness_rating`, `staff_rating`, `security_rating`, `review_text`, `status`) VALUES
(1, 'Engr. Bassey Edet', 'Port Harcourt, Rivers State', 'assets/img/testimonials/testimonials-img1.jpg', 5, 5, 5, 5, 'My 5-day stay at Tiano Hotel during our oil & gas project audit in Eket was flawless. The Diamond Room was spotless, high-speed Wi-Fi was rock-solid for remote meetings, and the complimentary breakfast was delicious every morning.', 'approved'),
(2, 'Dr. Victoria Nseobong', 'Uyo, Akwa Ibom', 'assets/img/testimonials/testimonials-img2.jpg', 5, 5, 5, 5, 'Tiano Hotel and Suites is easily the premier hospitality destination along Marina Road. The serenity, attentive front desk team, and top-tier security gave my family absolute peace of mind during our weekend retreat.', 'approved'),
(3, 'Chief Kenneth Okonkwo', 'Lagos, Nigeria', 'assets/img/testimonials/testimonials-img3.jpg', 5, 5, 5, 5, 'The Presidential Suite is truly world-class. From the private jacuzzi and prompt room service to the bush bar grills by the pool in the evening, everything exceeded expectations. Highly recommended for corporate executives.', 'approved');

-- ---------------------------------------------------------------------
-- 10. Table: inquiries
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `inquiries`;
CREATE TABLE `inquiries` (
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

INSERT INTO `inquiries` (`id`, `company_name`, `contact_person`, `email`, `phone`, `inquiry_type`, `start_date`, `end_date`, `estimated_guests`, `details`, `status`, `admin_notes`) VALUES
(1, 'ExxonMobil Contractor Services', 'Mr. David Mensah', 'd.mensah@contractor-logistics.com', '+234 803 555 0192', 'long_stay', DATE_ADD(CURDATE(), INTERVAL 14 DAY), DATE_ADD(CURDATE(), INTERVAL 45 DAY), 6, 'Requesting corporate pricing for 6 Diamond & Gold rooms for an offshore technical team on a 30-day rotation in Eket. Requires daily laundry, breakfast, and dedicated vehicle parking.', 'new', 'Contacted team via email; preparing customized corporate tariff proposal.');

-- ---------------------------------------------------------------------
-- 11. Table: admin_users
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `fullname` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin', 'manager', 'receptionist') DEFAULT 'receptionist',
  `last_login` DATETIME DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admin_users` (`id`, `username`, `fullname`, `email`, `password_hash`, `role`, `status`) VALUES
(1, 'admin', 'Tiano Hotel Administrator', 'admin@tianohotel.com', '$2y$10$s/erDBSHWFORh/3oaHax3ewdiMZn0LaElF1ks3HbM8ykd0wHC9BIu', 'super_admin', 'active'),
(2, 'frontdesk', 'Front Desk Reception', 'frontdesk@tianohotel.com', '$2y$10$s/erDBSHWFORh/3oaHax3ewdiMZn0LaElF1ks3HbM8ykd0wHC9BIu', 'receptionist', 'active');

-- ---------------------------------------------------------------------
-- 12. Table: site_settings
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `site_settings`;
CREATE TABLE `site_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `category` VARCHAR(50) DEFAULT 'general',
  `description` VARCHAR(255) DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `site_settings` (`setting_key`, `setting_value`, `category`, `description`) VALUES
('hotel_name', 'Tiano Hotel and Suites', 'general', 'Official trading name of the hotel'),
('hotel_tagline', 'Luxury, Comfort & Serenity in the Heart of Eket', 'general', 'Official brand tagline'),
('hotel_phone', '+234 814 473 4612', 'contact', 'Primary reception phone line'),
('hotel_alt_phone', '+234 803 762 1990', 'contact', 'Secondary manager contact line'),
('hotel_whatsapp', '2348144734612', 'contact', 'WhatsApp concierge direct phone number with country code'),
('hotel_email', 'tianohotel@gmail.com', 'contact', 'Official reservations and support email'),
('hotel_address', '44 Marina Road, Eket, Akwa Ibom State, Nigeria', 'contact', 'Physical hotel premises location'),
('checkin_time', '1:00 PM', 'operations', 'Standard daily check-in start time'),
('checkout_time', '12:00 PM', 'operations', 'Standard daily check-out deadline'),
('hold_expiry_time', '6:00 PM', 'operations', 'Cut-off time for unpaid reservations on arrival day'),
('caution_deposit_enabled', '0', 'finance', 'Toggle visibility of caution deposit on guest booking summary (1=Active, 0=Hidden)'),
('caution_deposit_default', '10000', 'finance', 'Default refundable incidental caution deposit in NGN'),
('currency_symbol', '₦', 'finance', 'Nigerian Naira currency display symbol'),
('currency_code', 'NGN', 'finance', 'Standard currency ISO code'),
('hero_title', 'Experience Royal Luxury & Serene Comfort in Eket', 'content', 'Main homepage hero headline'),
('hero_subtitle', 'Discover an unforgettable sanctuary of premium hospitality, exquisite dining, and world-class leisure at 44 Marina Road, Eket.', 'content', 'Homepage hero banner descriptive text'),
('facebook_url', 'https://facebook.com/tianohotel', 'social', 'Official Facebook page'),
('instagram_url', 'https://instagram.com/tianohotel', 'social', 'Official Instagram profile'),
('twitter_url', 'https://twitter.com/tianohotel', 'social', 'Official X (Twitter) profile');

-- ---------------------------------------------------------------------
-- 13. Table: audit_logs
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `entity_type` VARCHAR(50) NOT NULL,
  `entity_id` INT DEFAULT NULL,
  `details` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(50) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_audit_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `audit_logs` (`id`, `admin_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`) VALUES
(1, 1, 'system_initialized', 'system', 1, 'Tiano Hotel & Suites Database schema installed and initialized.', '127.0.0.1');

SET FOREIGN_KEY_CHECKS = 1;
