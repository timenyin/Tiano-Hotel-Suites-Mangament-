-- Table: site_settings
-- Global hotel configuration, contact information, operational policies and social metadata
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `category` VARCHAR(50) DEFAULT 'general',
  `description` VARCHAR(255) DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initial Settings Seed Data
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
('caution_deposit_default', '10000', 'finance', 'Default refundable incidental caution deposit in NGN'),
('currency_symbol', '₦', 'finance', 'Nigerian Naira currency display symbol'),
('currency_code', 'NGN', 'finance', 'Standard currency ISO code'),
('hero_title', 'Experience Royal Luxury & Serene Comfort in Eket', 'content', 'Main homepage hero headline'),
('hero_subtitle', 'Discover an unforgettable sanctuary of premium hospitality, exquisite dining, and world-class leisure at 44 Marina Road, Eket.', 'content', 'Homepage hero banner descriptive text'),
('facebook_url', 'https://facebook.com/tianohotel', 'social', 'Official Facebook page'),
('instagram_url', 'https://instagram.com/tianohotel', 'social', 'Official Instagram profile'),
('twitter_url', 'https://twitter.com/tianohotel', 'social', 'Official X (Twitter) profile')
ON DUPLICATE KEY UPDATE `setting_value`=VALUES(`setting_value`);
