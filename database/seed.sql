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
('maintenance_mode', '0')
ON DUPLICATE KEY UPDATE `setting_value`=VALUES(`setting_value`);
