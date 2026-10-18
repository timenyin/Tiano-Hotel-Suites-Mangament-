-- Table: rooms
-- Physical inventory of individual hotel rooms with real-time cleanliness and occupancy status
CREATE TABLE IF NOT EXISTS `rooms` (
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

-- Official Physical Rooms Seed Data across Tiano Hotel wings (Rooms 2 through 34)
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
(33, 3, '34', 2, 'clean', 'vacant', 'Second Floor - Diamond Room (VIP Upper Corner Suite)')
ON DUPLICATE KEY UPDATE 
  `category_id`=VALUES(`category_id`),
  `floor_number`=VALUES(`floor_number`),
  `notes`=VALUES(`notes`);
