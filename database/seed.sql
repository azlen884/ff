-- ==============================================================================
-- FF Panel V1/V2 Database Structure & Seed Data
-- Compatible with standalone MySQL import and existing PHP installer
-- ==============================================================================

-- 1. Users Table (with role, referral tracking, and wallet balance)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  `referral_code` VARCHAR(30) UNIQUE DEFAULT NULL,
  `referred_by` INT DEFAULT NULL,
  `wallet_balance` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_users_email` (`email`),
  INDEX `idx_users_role` (`role`),
  INDEX `idx_users_referral` (`referral_code`),
  INDEX `idx_users_referred_by` (`referred_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Wallet Transactions Table (Real MySQL Ledger)
CREATE TABLE IF NOT EXISTS `wallet_transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `type` ENUM('credit', 'debit') NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `balance_before` DECIMAL(10,2) NOT NULL,
  `balance_after` DECIMAL(10,2) NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `reference_id` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_wt_user` (`user_id`),
  INDEX `idx_wt_type` (`type`),
  INDEX `idx_wt_ref` (`reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Payment Gateways Table (Gateway Configurations)
CREATE TABLE IF NOT EXISTS `payment_gateways` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `is_enabled` TINYINT(1) DEFAULT 1,
  `instructions` TEXT DEFAULT NULL,
  `api_key` VARCHAR(255) DEFAULT NULL,
  `api_secret` VARCHAR(255) DEFAULT NULL,
  `merchant_id` VARCHAR(100) DEFAULT NULL,
  `upi_id` VARCHAR(100) DEFAULT NULL,
  `qr_image_url` VARCHAR(255) DEFAULT NULL,
  `min_deposit` DECIMAL(10,2) DEFAULT 50.00,
  `max_deposit` DECIMAL(10,2) DEFAULT 50000.00,
  `fee_percentage` DECIMAL(5,2) DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Payments Table (Deposit and Transaction Tracking)
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `gateway_code` VARCHAR(50) NOT NULL,
  `transaction_id` VARCHAR(100) NOT NULL UNIQUE,
  `gateway_txn_id` VARCHAR(100) DEFAULT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `fee` DECIMAL(10,2) DEFAULT 0.00,
  `net_amount` DECIMAL(10,2) NOT NULL,
  `status` ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
  `payment_details` TEXT DEFAULT NULL,
  `admin_note` VARCHAR(255) DEFAULT NULL,
  `verified_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_payments_user` (`user_id`),
  INDEX `idx_payments_status` (`status`),
  INDEX `idx_payments_txn` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. API Providers Table (Free Fire External Providers)
CREATE TABLE IF NOT EXISTS `providers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `api_url` VARCHAR(255) DEFAULT NULL,
  `api_key` VARCHAR(255) DEFAULT NULL,
  `api_secret` VARCHAR(255) DEFAULT NULL,
  `is_enabled` TINYINT(1) DEFAULT 0,
  `balance` DECIMAL(10,2) DEFAULT 0.00,
  `currency` VARCHAR(10) DEFAULT 'INR',
  `last_sync_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Saved Free Fire Player UIDs Table
CREATE TABLE IF NOT EXISTS `saved_uids` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `uid_number` VARCHAR(30) NOT NULL,
  `player_name` VARCHAR(100) DEFAULT NULL,
  `region` VARCHAR(50) DEFAULT 'India / Global',
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_saved_uids_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `icon_svg` TEXT DEFAULT NULL,
  `display_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Services Table (Products with provider mapping & margins)
CREATE TABLE IF NOT EXISTS `services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `provider_id` INT DEFAULT NULL,
  `provider_service_id` VARCHAR(100) DEFAULT NULL,
  `title` VARCHAR(150) NOT NULL,
  `subtitle` VARCHAR(150) NOT NULL DEFAULT 'Free Fire Diamonds',
  `description` TEXT DEFAULT NULL,
  `amount_description` VARCHAR(100) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `provider_cost` DECIMAL(10,2) DEFAULT 0.00,
  `admin_margin` DECIMAL(10,2) DEFAULT 0.00,
  `original_price` DECIMAL(10,2) DEFAULT NULL,
  `min_qty` INT DEFAULT 1,
  `max_qty` INT DEFAULT 1,
  `badge1` VARCHAR(50) DEFAULT 'Instant',
  `badge2` VARCHAR(50) DEFAULT 'Popular',
  `badge2_color` VARCHAR(50) DEFAULT 'red',
  `image_url` VARCHAR(255) DEFAULT NULL,
  `delivery_time` VARCHAR(50) DEFAULT 'Instant (1-5 Mins)',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `is_popular` TINYINT(1) NOT NULL DEFAULT 1,
  `display_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`provider_id`) REFERENCES `providers`(`id`) ON DELETE SET NULL,
  INDEX `idx_services_category` (`category_id`),
  INDEX `idx_services_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Coupons Table (Discount & Promotions)
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `discount_type` ENUM('percentage', 'flat') NOT NULL DEFAULT 'flat',
  `discount_value` DECIMAL(10,2) NOT NULL,
  `min_order_amount` DECIMAL(10,2) DEFAULT 0.00,
  `max_discount_amount` DECIMAL(10,2) DEFAULT NULL,
  `usage_limit` INT DEFAULT 100,
  `used_count` INT DEFAULT 0,
  `expires_at` DATE DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Orders Table (Order Lifecycle & Processing)
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `user_id` INT NOT NULL,
  `service_id` INT NOT NULL,
  `provider_id` INT DEFAULT NULL,
  `provider_order_id` VARCHAR(100) DEFAULT NULL,
  `player_uid` VARCHAR(50) NOT NULL,
  `player_name` VARCHAR(100) DEFAULT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `coupon_id` INT DEFAULT NULL,
  `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
  `payment_method` VARCHAR(50) NOT NULL DEFAULT 'Wallet Balance',
  `status` ENUM('pending', 'processing', 'completed', 'failed', 'cancelled') NOT NULL DEFAULT 'pending',
  `admin_note` TEXT DEFAULT NULL,
  `provider_response` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`provider_id`) REFERENCES `providers`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`coupon_id`) REFERENCES `coupons`(`id`) ON DELETE SET NULL,
  INDEX `idx_orders_user` (`user_id`),
  INDEX `idx_orders_status` (`status`),
  INDEX `idx_orders_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Referral Commissions Table
CREATE TABLE IF NOT EXISTS `referral_commissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `referrer_id` INT NOT NULL,
  `referred_user_id` INT NOT NULL,
  `order_id` INT DEFAULT NULL,
  `commission_amount` DECIMAL(10,2) NOT NULL,
  `commission_percentage` DECIMAL(5,2) NOT NULL,
  `status` ENUM('credited', 'pending') DEFAULT 'credited',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`referrer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`referred_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL,
  INDEX `idx_ref_referrer` (`referrer_id`),
  INDEX `idx_ref_referred` (`referred_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Notifications Table
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `role_target` VARCHAR(20) DEFAULT 'user',
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `type` VARCHAR(50) DEFAULT 'info',
  `is_read` TINYINT(1) DEFAULT 0,
  `link` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_notif_user` (`user_id`),
  INDEX `idx_notif_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Site Settings Table
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- REQUIRED APPLICATION CONFIGURATION & CATALOG DATA
-- (Non-destructive; safe to run on initial setup or upgrade)
-- ==============================================================================

-- Categories Data
INSERT INTO `categories` (`id`, `name`, `slug`, `display_order`) VALUES
(1, 'Diamonds', 'diamonds', 1),
(2, 'Membership', 'membership', 2),
(3, 'Elite Pass', 'elite-pass', 3),
(4, 'Character', 'character', 4),
(5, 'Weapon Skin', 'weapon-skin', 5),
(6, 'Bundle', 'bundle', 6),
(7, 'Pet', 'pet', 7),
(8, 'ID / UID', 'id-uid', 8),
(9, 'Special Offers', 'special-offers', 9)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `display_order`=VALUES(`display_order`);

-- Genuine Free Fire Store Services Catalog
INSERT INTO `services` (`id`, `category_id`, `title`, `subtitle`, `amount_description`, `price`, `provider_cost`, `admin_margin`, `original_price`, `badge1`, `badge2`, `badge2_color`, `delivery_time`, `is_active`, `is_popular`, `display_order`) VALUES
(1, 1, '100 Diamonds', 'Free Fire Diamonds', '100 Diamonds Topup', 20.00, 16.00, 4.00, 25.00, 'Instant', 'Top Selling', 'red', 'Instant (1-2 Mins)', 1, 1, 1),
(2, 1, '520 Diamonds', 'Free Fire Diamonds', '520 Diamonds Topup', 95.00, 80.00, 15.00, 110.00, 'Instant', 'Popular', 'red', 'Instant (1-2 Mins)', 1, 1, 2),
(3, 1, '1060 Diamonds', 'Free Fire Diamonds', '1060 Diamonds Topup', 180.00, 155.00, 25.00, 210.00, 'Instant', 'Best Value', 'emerald', 'Instant (1-2 Mins)', 1, 1, 3),
(4, 1, '2180 Diamonds', 'Free Fire Diamonds', '2180 Diamonds Topup', 340.00, 300.00, 40.00, 400.00, 'Instant', 'High Demand', 'red', 'Instant (1-2 Mins)', 1, 1, 4),
(5, 2, 'Weekly Membership', 'Free Fire Membership', 'Weekly 450 Diamonds + Badges', 70.00, 60.00, 10.00, 85.00, 'Instant', 'Popular', 'red', 'Instant (1-2 Mins)', 1, 1, 5),
(6, 2, 'Monthly Membership', 'Free Fire Membership', 'Monthly 2600 Diamonds', 199.00, 175.00, 24.00, 240.00, 'Instant', 'Best Value', 'emerald', 'Instant (1-2 Mins)', 1, 1, 6),
(7, 3, 'Elite Pass', 'Free Fire Elite Pass', 'Current Season Elite Pass Access', 120.00, 100.00, 20.00, 150.00, 'Instant', 'Trending', 'amber', 'Instant (1-2 Mins)', 1, 1, 7),
(8, 4, 'Character - Alok', 'Free Fire Character', 'DJ Alok Character Unlocked', 299.00, 260.00, 39.00, 399.00, 'Instant', 'Popular', 'red', 'Instant (1-2 Mins)', 1, 1, 8),
(9, 1, '5600 Diamonds', 'Free Fire Diamonds', 'Mega 5600 Diamonds Pack', 850.00, 770.00, 80.00, 999.00, 'Instant', 'Mega Value', 'emerald', 'Instant (1-2 Mins)', 1, 0, 9),
(10, 5, 'Evo Gun Token Box', 'Free Fire Weapon Skin', '100x Evo Upgrade Tokens', 150.00, 130.00, 20.00, 190.00, 'Instant', 'Hot', 'red', 'Instant (1-2 Mins)', 1, 0, 10),
(11, 7, 'Beaston Pet + Emote', 'Free Fire Pet', 'Helper Beaston Pet Pack', 110.00, 95.00, 15.00, 140.00, 'Instant', 'Rare', 'amber', 'Instant (1-2 Mins)', 1, 0, 11)
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`), `price`=VALUES(`price`), `provider_cost`=VALUES(`provider_cost`), `admin_margin`=VALUES(`admin_margin`);

-- Payment Gateways Configuration
INSERT INTO `payment_gateways` (`id`, `name`, `code`, `is_enabled`, `instructions`, `api_key`, `api_secret`, `merchant_id`, `upi_id`, `qr_image_url`, `min_deposit`, `max_deposit`, `fee_percentage`) VALUES
(1, 'Instant UPI / QR Code', 'upi_manual', 1, 'Scan QR or pay via any UPI App (GPay, PhonePe, Paytm) to the official UPI ID. Enter the 12-digit UTR / Transaction Reference Number to verify deposit.', NULL, NULL, NULL, 'ffpanel@upi', NULL, 50.00, 25000.00, 0.00),
(2, 'Razorpay Gateway', 'razorpay', 0, 'Card, Netbanking, UPI, and Wallets via Razorpay Checkout. Set API Key & Secret in Admin Settings to activate.', NULL, NULL, NULL, NULL, NULL, 100.00, 50000.00, 2.00),
(3, 'Paytm Payment Gateway', 'paytm', 0, 'Direct Paytm Wallet & UPI payment integration. Set Merchant ID & Key in Admin Settings to activate.', NULL, NULL, NULL, NULL, NULL, 100.00, 50000.00, 1.50)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `instructions`=VALUES(`instructions`);

-- API Providers Configuration
INSERT INTO `providers` (`id`, `name`, `code`, `api_url`, `api_key`, `api_secret`, `is_enabled`, `balance`, `currency`) VALUES
(1, 'Garena Direct API Partner', 'garena_direct', 'https://api.garena-services.com/v1', NULL, NULL, 0, 0.00, 'INR'),
(2, 'Smile One Free Fire API', 'smile_one', 'https://api.smileone.com/v1/ff', NULL, NULL, 0, 0.00, 'INR')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `api_url`=VALUES(`api_url`);

-- Starter Discount Coupons
INSERT INTO `coupons` (`id`, `code`, `discount_type`, `discount_value`, `min_order_amount`, `max_discount_amount`, `usage_limit`, `used_count`, `is_active`) VALUES
(1, 'WELCOME10', 'percentage', 10.00, 100.00, NULL, 500, 0, 1),
(2, 'FF50', 'flat', 50.00, 450.00, NULL, 200, 0, 1),
(3, 'MEGA100', 'flat', 100.00, 800.00, NULL, 100, 0, 1)
ON DUPLICATE KEY UPDATE `code`=VALUES(`code`), `discount_value`=VALUES(`discount_value`);

-- System & Website Configuration Settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'FF Panel Store'),
('site_tagline', 'Fast • Safe • Reliable'),
('currency_symbol', '₹'),
('announcement', 'Flash Sale: Flat 15% Bonus Diamonds on all Orders above ₹500 today!'),
('support_email', 'support@ffpanel.com'),
('support_whatsapp', '+91 98765 43210'),
('maintenance_mode', '0'),
('referral_enabled', '1'),
('referral_commission_percent', '5.00'),
('min_deposit_amount', '50.00'),
('max_deposit_amount', '50000.00'),
('instant_delivery_mode', '1')
ON DUPLICATE KEY UPDATE `setting_value`=VALUES(`setting_value`);
