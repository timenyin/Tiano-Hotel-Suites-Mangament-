-- Table: newsletter_subscribers
-- Newsletter email subscriptions collected from website footer forms
CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `status` ENUM('active', 'unsubscribed') DEFAULT 'active',
  `subscribed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample newsletter subscriber
INSERT INTO `newsletter_subscribers` (`id`, `email`, `status`) VALUES
(1, 'guest.relations@tianohotel.com', 'active')
ON DUPLICATE KEY UPDATE `email`=VALUES(`email`);
