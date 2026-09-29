USE `ffpanel`;

-- Insert Demo Customer User (Aaris Ali matching reference image)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `role`, `wallet_balance`, `status`) VALUES
(2, 'Aaris Ali', 'aaris@ffpanel.com', '$2y$10$BgdpPSsU06oUMigTDvpdAeLtyPwf2AI6WROvHtoQKtCY9Gr7f6GiG', '+91 9123456789', 'user', 1250.00, 'active')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- Insert Saved Free Fire UIDs for demo user
INSERT INTO `saved_uids` (`id`, `user_id`, `uid_number`, `player_name`, `region`, `is_default`) VALUES
(1, 2, '2849182391', 'Aaris_Gaming_YT', 'India Server', 1),
(2, 2, '1938501248', 'ShadowNinja_FF', 'India Server', 0)
ON DUPLICATE KEY UPDATE `player_name`=VALUES(`player_name`);

-- Insert Categories matching reference image
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
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- Insert Services matching reference image exactly
INSERT INTO `services` (`id`, `category_id`, `title`, `subtitle`, `amount_description`, `price`, `original_price`, `badge1`, `badge2`, `badge2_color`, `delivery_time`, `is_active`, `is_popular`, `display_order`) VALUES
(1, 1, '100 Diamonds', 'Free Fire Diamonds', '100 Diamonds Topup', 20.00, 25.00, 'Instant', 'Top Selling', 'red', 'Instant (1-2 Mins)', 1, 1, 1),
(2, 1, '520 Diamonds', 'Free Fire Diamonds', '520 Diamonds Topup', 95.00, 110.00, 'Instant', 'Popular', 'red', 'Instant (1-2 Mins)', 1, 1, 2),
(3, 1, '1060 Diamonds', 'Free Fire Diamonds', '1060 Diamonds Topup', 180.00, 210.00, 'Instant', 'Best Value', 'emerald', 'Instant (1-2 Mins)', 1, 1, 3),
(4, 1, '2180 Diamonds', 'Free Fire Diamonds', '2180 Diamonds Topup', 340.00, 400.00, 'Instant', 'High Demand', 'red', 'Instant (1-2 Mins)', 1, 1, 4),
(5, 2, 'Weekly Membership', 'Free Fire Membership', 'Weekly 450 Diamonds + Badges', 70.00, 85.00, 'Instant', 'Popular', 'red', 'Instant (1-2 Mins)', 1, 1, 5),
(6, 2, 'Monthly Membership', 'Free Fire Membership', 'Monthly 2600 Diamonds', 199.00, 240.00, 'Instant', 'Best Value', 'emerald', 'Instant (1-2 Mins)', 1, 1, 6),
(7, 3, 'Elite Pass', 'Free Fire Elite Pass', 'Current Season Elite Pass Access', 120.00, 150.00, 'Instant', 'Trending', 'amber', 'Instant (1-2 Mins)', 1, 1, 7),
(8, 4, 'Character - Alok', 'Free Fire Character', 'DJ Alok Character Unlocked', 299.00, 399.00, 'Instant', 'Popular', 'red', 'Instant (1-2 Mins)', 1, 1, 8),
(9, 1, '5600 Diamonds', 'Free Fire Diamonds', 'Mega 5600 Diamonds Pack', 850.00, 999.00, 'Instant', 'Mega Value', 'emerald', 'Instant (1-2 Mins)', 1, 0, 9),
(10, 5, 'Evo Gun Token Box', 'Free Fire Weapon Skin', '100x Evo Upgrade Tokens', 150.00, 190.00, 'Instant', 'Hot', 'red', 'Instant (1-2 Mins)', 1, 0, 10),
(11, 7, 'Beaston Pet + Emote', 'Free Fire Pet', 'Helper Beaston Pet Pack', 110.00, 140.00, 'Instant', 'Rare', 'amber', 'Instant (1-2 Mins)', 1, 0, 11)
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`);

-- Insert Initial Real Orders for user and live activity (Rahul Sharma, Sneha Patel, Rohit Kumar, Aman Khan, Pooja Singh, Aaris Ali)
INSERT INTO `orders` (`id`, `order_number`, `user_id`, `service_id`, `player_uid`, `player_name`, `amount`, `payment_method`, `status`, `created_at`) VALUES
(1, 'FF-2026-9041', 2, 2, '2849182391', 'Rahul Sharma', 95.00, 'Wallet Balance', 'completed', DATE_SUB(NOW(), INTERVAL 2 MINUTE)),
(2, 'FF-2026-9040', 2, 5, '1938501248', 'Sneha Patel', 70.00, 'Wallet Balance', 'completed', DATE_SUB(NOW(), INTERVAL 5 MINUTE)),
(3, 'FF-2026-9039', 2, 3, '3059182049', 'Rohit Kumar', 180.00, 'Wallet Balance', 'completed', DATE_SUB(NOW(), INTERVAL 8 MINUTE)),
(4, 'FF-2026-9038', 2, 7, '4102948210', 'Aman Khan', 120.00, 'Wallet Balance', 'completed', DATE_SUB(NOW(), INTERVAL 12 MINUTE)),
(5, 'FF-2026-9037', 2, 4, '5291830192', 'Pooja Singh', 340.00, 'Wallet Balance', 'completed', DATE_SUB(NOW(), INTERVAL 15 MINUTE)),
(6, 'FF-2026-9036', 2, 1, '2849182391', 'Aaris Ali', 20.00, 'Wallet Balance', 'completed', DATE_SUB(NOW(), INTERVAL 35 MINUTE))
ON DUPLICATE KEY UPDATE `order_number`=VALUES(`order_number`);

-- Insert Site Settings
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

-- Insert Payment Gateways (UPI, Razorpay, Paytm)
INSERT INTO `payment_gateways` (`id`, `name`, `code`, `is_enabled`, `instructions`, `api_key`, `api_secret`, `merchant_id`, `upi_id`, `qr_image_url`, `min_deposit`, `max_deposit`, `fee_percentage`) VALUES
(1, 'Instant UPI / QR Code', 'upi_manual', 1, 'Scan QR or pay via any UPI App (GPay, PhonePe, Paytm) to the official UPI ID. Enter the 12-digit UTR / Transaction Reference Number to verify deposit.', NULL, NULL, NULL, 'ffpanel@upi', NULL, 50.00, 25000.00, 0.00),
(2, 'Razorpay Gateway', 'razorpay', 0, 'Card, Netbanking, UPI, and Wallets via Razorpay Checkout. Set API Key & Secret in Admin Settings to activate.', NULL, NULL, NULL, NULL, NULL, 100.00, 50000.00, 2.00),
(3, 'Paytm Payment Gateway', 'paytm', 0, 'Direct Paytm Wallet & UPI payment integration. Set Merchant ID & Key in Admin Settings to activate.', NULL, NULL, NULL, NULL, NULL, 100.00, 50000.00, 1.50)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- Insert API Providers (Garena Direct, Smile One)
INSERT INTO `providers` (`id`, `name`, `code`, `api_url`, `api_key`, `api_secret`, `is_enabled`, `balance`, `currency`) VALUES
(1, 'Garena Direct API Partner', 'garena_direct', 'https://api.garena-services.com/v1', NULL, NULL, 0, 0.00, 'INR'),
(2, 'Smile One Free Fire API', 'smile_one', 'https://api.smileone.com/v1/ff', NULL, NULL, 0, 0.00, 'INR')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- Insert Discount Coupons
INSERT INTO `coupons` (`id`, `code`, `discount_type`, `discount_value`, `min_order_amount`, `max_discount_amount`, `usage_limit`, `used_count`, `is_active`) VALUES
(1, 'WELCOME10', 'percentage', 10.00, 100.00, NULL, 500, 0, 1),
(2, 'FF50', 'flat', 50.00, 450.00, NULL, 200, 0, 1),
(3, 'MEGA100', 'flat', 100.00, 800.00, NULL, 100, 0, 1)
ON DUPLICATE KEY UPDATE `code`=VALUES(`code`);

-- Insert Initial Wallet Transactions for demo user
INSERT INTO `wallet_transactions` (`id`, `user_id`, `type`, `amount`, `balance_before`, `balance_after`, `description`, `reference_id`, `created_at`) VALUES
(1, 2, 'credit', 1500.00, 0.00, 1500.00, 'UPI Deposit Approval (UTR: 329183019281)', 'DEP-98124', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(2, 2, 'debit', 95.00, 1500.00, 1405.00, 'Order #FF-2026-9041 - 520 Diamonds', 'ORD-FF-2026-9041', DATE_SUB(NOW(), INTERVAL 45 MINUTE)),
(3, 2, 'debit', 70.00, 1405.00, 1335.00, 'Order #FF-2026-9040 - Weekly Membership', 'ORD-FF-2026-9040', DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(4, 2, 'debit', 85.00, 1335.00, 1250.00, 'Order #FF-2026-9036 - 100 Diamonds & Token', 'ORD-FF-2026-9036', DATE_SUB(NOW(), INTERVAL 10 MINUTE))
ON DUPLICATE KEY UPDATE `description`=VALUES(`description`);

-- Insert Initial Notifications for demo user
INSERT INTO `notifications` (`id`, `user_id`, `role_target`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES
(1, 2, 'user', 'Welcome to FF Panel Store!', 'Your account has been activated. Top up diamonds instantly with 0% gateway fee today.', 'success', 1, '/services', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 2, 'user', 'Deposit Approved: ₹1,500.00', 'Your UPI deposit has been verified and added to your wallet balance.', 'success', 0, '/wallet', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(3, 2, 'user', 'Order #FF-2026-9041 Completed', 'Your 520 Diamonds top-up for UID 2849182391 was delivered successfully.', 'success', 0, '/order-detail?id=1', DATE_SUB(NOW(), INTERVAL 45 MINUTE))
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`);
