-- Create budget goals table
CREATE TABLE `budget_goals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `target_amount` decimal(10,2) NOT NULL,
  `current_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `target_date` date NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add primary key
ALTER TABLE `budget_goals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

-- Add foreign key constraint
ALTER TABLE `budget_goals`
  ADD CONSTRAINT `budget_goals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- Add auto increment
ALTER TABLE `budget_goals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
