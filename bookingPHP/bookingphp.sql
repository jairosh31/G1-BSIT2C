-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 23, 2026 at 10:00 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bookingphp`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `service_type` varchar(50) NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `name`, `email`, `phone`, `service_type`, `booking_date`, `booking_time`, `message`, `status`, `created_at`) VALUES
(1, 2, 'reyy', 'reyy@gmail.com', 'reyy', 'Consultation', '2026-01-30', '11:44:00', 'wadahel', 'pending', '2026-01-30 03:42:54'),
(2, 5, 'reyy', 'rey@gmail.com', '012839813', 'Consultation', '2026-02-14', '03:40:00', 'yeah yeah', 'cancelled', '2026-01-30 03:46:27');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `event_date` date NOT NULL,
  `location` varchar(150) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `regular_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `vip_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `name`, `event_date`, `location`, `price`, `regular_price`, `vip_price`, `is_featured`, `created_at`) VALUES
(1, 'THE MUSIC RUN - MANILA', '2026-05-19', 'Filinvest City, Event Grounds', 2000.00, 0.00, 0.00, 0, '2026-04-17 05:07:18'),
(2, 'THE MUSIC RUN - MANILA', '2026-04-30', 'Filinvest City, Event Grounds', 2000.00, 2000.00, 3500.00, 1, '2026-04-17 05:24:47'),
(3, 'Justin Padaplin Concert', '2026-04-30', 'Las Vegas', 5000.00, 5000.00, 7500.00, 1, '2026-04-17 08:32:33');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `source` enum('php','java') DEFAULT 'php',
  `rating` tinyint(4) NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`id`, `user_id`, `source`, `rating`, `comment`, `created_at`) VALUES
(1, 9, 'php', 5, 'REYJOMAR IS THE BEST', '2026-02-12 16:32:53'),
(2, 9, 'php', 5, 'THE BEST KUPAL REYJOMAR', '2026-02-12 16:50:39'),
(3, 4, 'php', 5, '', '2026-02-13 03:40:10'),
(4, 10, 'php', 4, 'Jai', '2026-02-13 05:09:43'),
(5, 10, 'php', 2, 'jareb', '2026-02-13 05:09:59'),
(6, 11, 'php', 5, 'so amazing', '2026-04-17 05:49:25'),
(7, 13, 'php', 5, 'wow wow nindota oy', '2026-04-17 08:30:25'),
(8, 13, 'php', 2, 'gege ha', '2026-04-17 08:30:42');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_name` varchar(100) NOT NULL,
  `event_date` date NOT NULL,
  `event_location` varchar(120) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `ticket_code` varchar(50) NOT NULL,
  `chair_number` varchar(50) DEFAULT NULL,
  `seat_type` enum('Regular','VIP') DEFAULT 'Regular',
  `payment_method` enum('Cash','Credit Card','Gcash','Paymaya') DEFAULT 'Cash',
  `status` enum('active','cancelled') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `user_id`, `event_name`, `event_date`, `event_location`, `quantity`, `price`, `ticket_code`, `chair_number`, `seat_type`, `payment_method`, `status`, `created_at`) VALUES
(1, 9, 'Reyjomar Concert', '2025-02-14', NULL, 1, NULL, 'TKT-B1D6667A', NULL, 'Regular', 'Cash', 'cancelled', '2026-02-12 16:28:00'),
(2, 9, 'Reyjomar Concert', '2026-02-14', NULL, 5, NULL, 'TKT-7BD8057E', NULL, 'Regular', 'Cash', 'active', '2026-02-12 16:50:19'),
(3, 10, 'Aranas Trip', '2026-02-14', NULL, 5, NULL, 'TKT-B11D3A77', NULL, 'Regular', 'Cash', 'cancelled', '2026-02-12 16:55:49'),
(4, 4, 'jaijaiconcert', '2026-02-13', NULL, 50, NULL, 'TKT-7E368BEF', NULL, 'Regular', 'Cash', 'cancelled', '2026-02-13 03:41:48'),
(5, 10, 'THE MUSIC RUN - MANILA', '2026-05-19', 'Filinvest City, Event Grounds', 1, 2000.00, 'TKT-C04F52B5', NULL, 'Regular', 'Cash', 'active', '2026-04-17 05:07:39'),
(6, 10, 'THE MUSIC RUN - MANILA', '2026-05-19', 'Filinvest City, Event Grounds', 1, 2000.00, 'TKT-AF9B15B2', NULL, 'Regular', 'Cash', 'active', '2026-04-17 05:09:14'),
(7, 11, 'THE MUSIC RUN - MANILA', '2026-05-19', 'Filinvest City, Event Grounds', 1, 2000.00, 'TKT-D0265C78', NULL, 'Regular', 'Cash', 'active', '2026-04-17 05:09:43'),
(8, 11, 'THE MUSIC RUN - MANILA', '2026-04-30', 'Filinvest City, Event Grounds', 1, 3500.00, 'TKT-4662206F', '450', 'VIP', 'Cash', 'active', '2026-04-17 05:25:29'),
(9, 11, 'THE MUSIC RUN - MANILA', '2026-04-30', 'Filinvest City, Event Grounds', 2, 2000.00, 'TKT-7CEAFF08', '451', 'Regular', 'Cash', 'active', '2026-04-17 05:26:13'),
(10, 11, 'THE MUSIC RUN - MANILA', '2026-04-30', 'Filinvest City, Event Grounds', 1, 3500.00, 'TKT-6E0307C0', '19', 'VIP', 'Gcash', 'active', '2026-04-17 05:28:59'),
(11, 12, 'THE MUSIC RUN - MANILA', '2026-04-30', 'Filinvest City, Event Grounds', 1, 3500.00, 'TKT-59F930D9', '40', 'VIP', 'Cash', 'active', '2026-04-17 07:38:14'),
(12, 13, 'THE MUSIC RUN - MANILA', '2026-04-30', 'Filinvest City, Event Grounds', 1, 3500.00, 'TKT-0E938327', '1', 'VIP', 'Cash', 'active', '2026-04-17 08:29:58'),
(13, 11, 'THE MUSIC RUN - MANILA', '2026-04-30', 'Filinvest City, Event Grounds', 1, 3500.00, 'TKT-0FBE4122', 'Chair 15 (VIP - Center Row A)', 'VIP', 'Cash', 'active', '2026-04-23 07:44:41');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'rey', 'rey@gmail.com', '$2y$10$0wNgDtLBh7HT5HqnqC51XeqZLWDQBfEIf/f1ssWKB6MowfJvMuy2i', 'user', '2026-01-30 03:06:25'),
(2, 'reyy', 'reyy@gmail.com', '$2y$10$69ARXO8wge/2VFQ3pKqsE.wlOYkHTVmupl3tDsWcFdKiS1DoV6VVO', 'user', '2026-01-30 03:06:48'),
(3, 'jairosh', 'jairosh@gmail.com', '$2y$10$eystvE5HLtYap2jM20AedOKz/EhgoTjbgu7j5GURImf8va8nTHKdu', 'user', '2026-01-30 03:10:35'),
(4, 'sa', 'sa@gmai', '$2y$10$QlyE.GAPYr9fT8fYS7OREe0ULxkniNtEQZjhjsWGQQn6p1BkBIOCK', 'user', '2026-01-30 03:32:54'),
(5, 'reyjomar', 'aranas@gmail.com', '$2y$10$O94kWGHEhe7BDZsb7bLt9.JscTEnT0C0314f4xE/ZmUekgJeVjg86', 'user', '2026-01-30 03:44:39'),
(6, 'jomar', 'jomar@gmail.com', '$2y$10$GG/kcm1ExJj8JOBSdkuWZ.ruxQ0KNW.2Sjw2uphoMemGxEGbs98Zq', 'user', '2026-01-30 04:34:59'),
(7, 'reyjomar123', '', '$2y$10$g0RYDXea083fEl74m6OZOurJg.yRftAmMF5YT5ogmKTsFqRrKFT52', 'user', '2026-02-12 15:53:32'),
(9, 'jareb', 'jarebmurillo123@gmail.com', '$2y$10$Rkt82v5LlItE3aVU2EZj5eASwDjh2O6OC2DszOq64/9ngY6v1MLEy', 'user', '2026-02-12 16:02:18'),
(10, 'admin', 'admin@gmail.com', '$2y$10$1HB2p5qc5aYEakgzFunqFez8VtA4n9FQVf8I5ly4oIM1eYY1dGBqe', 'admin', '2026-02-12 16:41:06'),
(11, 'jareb123', 'jareb123@gmail.com', '$2y$10$V2sUFqaL8ukvTkHku03PwuOKXocD3lvnxaVys9fK8G7ik4m0g9JRu', 'user', '2026-04-17 04:46:49'),
(12, 'jaijai', 'jai@gmail.com', '$2y$10$DAoiZzoV5fKiPNCZJoPFeuVf1hN33o8GWcF4PJGcTIkS4nDNxISlC', 'user', '2026-04-17 07:34:15'),
(13, 'kengkeng', 'keng@gmail.com', '$2y$10$ZQEfzAKmlGOeXvJPaDoNl.SuKjnZsXg/A3OQxYS1G4JVE7jwKplNy', 'user', '2026-04-17 08:26:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_booking_date` (`booking_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ratings_user` (`user_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_code` (`ticket_code`),
  ADD KEY `fk_tickets_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `fk_ratings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `fk_tickets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
