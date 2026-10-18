-- Table: faqs
-- Frequently asked questions displayed on homepage and room detail pages
CREATE TABLE IF NOT EXISTS `faqs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `question` VARCHAR(255) NOT NULL,
  `answer` TEXT NOT NULL,
  `category` VARCHAR(50) DEFAULT 'general',
  `sort_order` INT DEFAULT 0,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initial FAQs Seed Data
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `sort_order`, `status`) VALUES
(1, 'What is the payment policy for online reservations?', 'We operate a transparent Pay-on-Arrival policy. When you make an online booking, a digital voucher is generated with your unique booking reference. You settle payment physically at our front desk upon arrival using POS terminal, cash, or verified instant bank transfer.', 'booking', 1, 'active'),
(2, 'What time is standard check-in and check-out?', 'Standard check-in commences at 1:00 PM, and check-out is by 12:00 PM (noon). Early check-in or late check-out requests are accommodated based on room availability and may attract an extended stay charge.', 'stay', 2, 'active'),
(3, 'Is complimentary breakfast included with all room categories?', 'Yes! Every room booking at Tiano Hotel and Suites includes our complimentary gourmet breakfast banquet served daily from 6:30 AM to 10:00 AM at our restaurant.', 'dining', 3, 'active'),
(4, 'How secure is the hotel premises on Marina Road, Eket?', 'We maintain 24-hour perimeter security patrols, monitored high-definition CCTV surveillance, secure access control gates, and private guarded on-site parking for all our guests.', 'security', 4, 'active'),
(5, 'Do you host corporate events and private celebrations?', 'Yes, our modern conference halls and scenic poolside bush bar are fully equipped with multimedia systems and dedicated banqueting catering for conferences, retreats, and social functions.', 'events', 5, 'active')
ON DUPLICATE KEY UPDATE `question`=VALUES(`question`);
