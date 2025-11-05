-- Add is_premium column to users table
ALTER TABLE `users` ADD COLUMN `is_premium` TINYINT(1) NOT NULL DEFAULT 0 AFTER `password_hash`;

