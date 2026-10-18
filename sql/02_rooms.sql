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

-- Initial Physical Rooms Seed Data across Tiano Hotel wings
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
(14, 5, 'PH-01', 5, 'inspected', 'vacant', 'Penthouse Level - Presidential Suite VIP Wing')
ON DUPLICATE KEY UPDATE `room_number`=VALUES(`room_number`), `cleanliness_status`=VALUES(`cleanliness_status`);
