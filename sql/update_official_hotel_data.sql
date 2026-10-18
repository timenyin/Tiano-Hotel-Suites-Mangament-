-- =====================================================================
-- TIANO HOTEL AND SUITES - OFFICIAL MANAGEMENT DATA UPDATE
-- Applied: 2026-10-18
-- Reflects verified rates, 6 room categories, 33 physical rooms, and policies
-- =====================================================================

USE `tiano_hotel_db`;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Update existing room categories & insert Silver Room
UPDATE `room_categories` SET `price_per_night` = 35000.00 WHERE `id` = 1;
UPDATE `room_categories` SET `price_per_night` = 50000.00 WHERE `id` = 2;
UPDATE `room_categories` SET `price_per_night` = 60000.00 WHERE `id` = 3;
UPDATE `room_categories` SET `price_per_night` = 70000.00 WHERE `id` = 4;
UPDATE `room_categories` SET `price_per_night` = 150000.00 WHERE `id` = 5;

INSERT INTO `room_categories` (`id`, `name`, `slug`, `tagline`, `price_per_night`, `capacity_adults`, `capacity_children`, `bed_type`, `room_size`, `description`, `amenities`, `featured_image`, `status`)
VALUES (6, 'Silver Room', 'silver-room', 'Essential Elegance & Modern Convenience', 45000.00, 2, 1, 'Queen Bed', '30 sqm', 
'The Silver Room combines understated luxury with modern essentials. Ideal for corporate professionals and travelers seeking comfort, high-speed fiber internet, pristine air conditioning, and convenient floor access.',
'Free High-Speed Wi-Fi, 43" Smart TV with DSTV, Individual Climate Control, Complimentary Gourmet Breakfast, En-Suite Walk-In Shower, Work Desk, 24/7 Room Service',
'assets/img/room/room-img10.png', 'active')
ON DUPLICATE KEY UPDATE 
  `name` = VALUES(`name`),
  `slug` = VALUES(`slug`),
  `price_per_night` = VALUES(`price_per_night`),
  `tagline` = VALUES(`tagline`),
  `description` = VALUES(`description`),
  `amenities` = VALUES(`amenities`),
  `featured_image` = VALUES(`featured_image`),
  `status` = 'active';

-- 2. Clear room assignments in bookings before rebuilding rooms table
UPDATE `bookings` SET `assigned_room_id` = NULL WHERE `assigned_room_id` IS NOT NULL;

-- 3. Truncate & reseed the physical rooms table with all 33 real rooms (Rooms 2 through 34)
TRUNCATE TABLE `rooms`;

INSERT INTO `rooms` (`id`, `category_id`, `room_number`, `floor_number`, `cleanliness_status`, `occupancy_status`, `notes`) VALUES
-- Ground Floor (Rooms 2 - 10)
(1,  2, '2',  0, 'clean', 'vacant', 'Ground Floor - Gold Room (Garden & Courtyard Access)'),
(2,  2, '3',  0, 'clean', 'vacant', 'Ground Floor - Gold Room (Quiet Courtyard Side)'),
(3,  3, '4',  0, 'clean', 'vacant', 'Ground Floor - Diamond Room (Spacious Luxury Suite)'),
(4,  3, '5',  0, 'clean', 'vacant', 'Ground Floor - Diamond Room (Executive Corner)'),
(5,  6, '6',  0, 'clean', 'vacant', 'Ground Floor - Silver Room (Near Reception & Lobby)'),
(6,  2, '7',  0, 'clean', 'vacant', 'Ground Floor - Gold Room (Easy Access Wing)'),
(7,  6, '8',  0, 'clean', 'vacant', 'Ground Floor - Silver Room (Quiet Inner Corridor)'),
(8,  2, '9',  0, 'clean', 'vacant', 'Ground Floor - Gold Room (Poolside Access Corridor)'),
(9,  6, '10', 0, 'clean', 'vacant', 'Ground Floor - Silver Room (West Wing Comfort)'),

-- First Floor (Rooms 11 - 22)
(10, 4, '11', 1, 'clean', 'vacant', 'First Floor - Premium Room (Executive View Wing)'),
(11, 6, '12', 1, 'clean', 'vacant', 'First Floor - Silver Room (Convenient Stairwell Access)'),
(12, 3, '13', 1, 'clean', 'vacant', 'First Floor - Diamond Room (Panoramic Balcony View)'),
(13, 1, '14', 1, 'clean', 'vacant', 'First Floor - Coral Room (Plush Modern Sanctuary)'),
(14, 6, '15', 1, 'clean', 'vacant', 'First Floor - Silver Room (Mid-Corridor Silence)'),
(15, 6, '16', 1, 'clean', 'vacant', 'First Floor - Silver Room (East Facing Morning Sun)'),
(16, 6, '17', 1, 'clean', 'vacant', 'First Floor - Silver Room (Central Living Comfort)'),
(17, 4, '18', 1, 'clean', 'vacant', 'First Floor - Premium Room (Expansive Lounge Layout)'),
(18, 2, '19', 1, 'clean', 'vacant', 'First Floor - Gold Room (Spacious King Comfort)'),
(19, 6, '20', 1, 'clean', 'vacant', 'First Floor - Silver Room (Quiet Residential Corridor)'),
(20, 6, '21', 1, 'clean', 'vacant', 'First Floor - Silver Room (Modern Executive Trim)'),
(21, 2, '22', 1, 'clean', 'vacant', 'First Floor - Gold Room (Marina Road Facing Vista)'),

-- Second Floor (Rooms 23 - 34)
(22, 4, '23', 2, 'clean', 'vacant', 'Second Floor - Premium Room (Elevated Executive Suite)'),
(23, 2, '24', 2, 'clean', 'vacant', 'Second Floor - Gold Room (High Vista Corner Unit)'),
(24, 3, '25', 2, 'clean', 'vacant', 'Second Floor - Diamond Room (Luxury Soaking Tub)'),
(25, 1, '26', 2, 'clean', 'vacant', 'Second Floor - Coral Room (Top Floor Quiet Sanctuary)'),
(26, 3, '27', 2, 'clean', 'vacant', 'Second Floor - Diamond Room (Panoramic Skyline View)'),
(27, 5, '28', 2, 'inspected', 'vacant', 'Second Floor - Presidential Suite (Aristocratic Penthouse Wing)'),
(28, 2, '29', 2, 'clean', 'vacant', 'Second Floor - Gold Room (Top Floor City Views)'),
(29, 6, '30', 2, 'clean', 'vacant', 'Second Floor - Silver Room (Top Floor Executive Unit)'),
(30, 2, '31', 2, 'clean', 'vacant', 'Second Floor - Gold Room (Quiet Retreat)'),
(31, 6, '32', 2, 'clean', 'vacant', 'Second Floor - Silver Room (Upper Deck Sanctuary)'),
(32, 6, '33', 2, 'clean', 'vacant', 'Second Floor - Silver Room (Elevated Quiet Corridor)'),
(33, 3, '34', 2, 'clean', 'vacant', 'Second Floor - Diamond Room (VIP Upper Corner Suite)');

-- 4. Update Official Contact & Policy Settings in site_settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `category`, `description`) VALUES
('hotel_phone', '08144734612', 'contact', 'Official front desk phone number'),
('hotel_email', 'support@tianohotelandsuites.com', 'contact', 'Official public and guest inquiries email'),

('hotel_whatsapp', '2348144734612', 'contact', 'Official WhatsApp concierge direct number'),
('checkin_time', '1:00 PM', 'operations', 'Standard guest check-in start time'),
('checkout_time', '12:00 PM', 'operations', 'Standard guest check-out deadline'),
('cancellation_policy', '48 hours before arrival warrants 100% refund. 24 hours before arrival attracts 20% deduction. Day of arrival cancellation attracts 50% deduction. Cash refunded by cash; card/transfer refunded by transfer.', 'operations', 'Official guest cancellation and refund policy'),
('late_checkout_policy', 'Late check-out after 16:00 (4:00 PM) attracts 50% room charges, thereafter full room rate shall be charged.', 'operations', 'Official late check-out fee rule'),
('house_rules', 'Food and Beverages from outside are not allowed. Firearms brought by Law Enforcement Agents must be disarmed. No pets allowed.', 'operations', 'Official hotel premises rules')
ON DUPLICATE KEY UPDATE 
  `setting_value` = VALUES(`setting_value`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

SET FOREIGN_KEY_CHECKS = 1;
