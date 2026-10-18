-- Table: reviews
-- Guest testimonials and verified ratings with moderation workflow
CREATE TABLE IF NOT EXISTS `reviews` (
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

-- Initial Authentic Guest Reviews Seed Data
INSERT INTO `reviews` (`id`, `guest_name`, `guest_location`, `guest_photo`, `rating`, `cleanliness_rating`, `staff_rating`, `security_rating`, `review_text`, `status`) VALUES
(1, 'Engr. Bassey Edet', 'Port Harcourt, Rivers State', 'assets/img/testimonials/testimonials-img1.jpg', 5, 5, 5, 5, 'My 5-day stay at Tiano Hotel during our oil & gas project audit in Eket was flawless. The Diamond Room was spotless, high-speed Wi-Fi was rock-solid for remote meetings, and the complimentary breakfast was delicious every morning.', 'approved'),
(2, 'Dr. Victoria Nseobong', 'Uyo, Akwa Ibom', 'assets/img/testimonials/testimonials-img2.jpg', 5, 5, 5, 5, 'Tiano Hotel and Suites is easily the premier hospitality destination along Marina Road. The serenity, attentive front desk team, and top-tier security gave my family absolute peace of mind during our weekend retreat.', 'approved'),
(3, 'Chief Kenneth Okonkwo', 'Lagos, Nigeria', 'assets/img/testimonials/testimonials-img3.jpg', 5, 5, 5, 5, 'The Presidential Suite is truly world-class. From the private jacuzzi and prompt room service to the bush bar grills by the pool in the evening, everything exceeded expectations. Highly recommended for corporate executives.', 'approved')
ON DUPLICATE KEY UPDATE `guest_name`=VALUES(`guest_name`), `review_text`=VALUES(`review_text`);
