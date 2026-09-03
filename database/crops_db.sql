-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 04, 2026 at 12:00 AM
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
-- Database: `crops_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_warnings`
--

CREATE TABLE `admin_warnings` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `warning_message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `agro_ecological_zones`
--

CREATE TABLE `agro_ecological_zones` (
  `id` int(11) NOT NULL,
  `zone_name` varchar(100) NOT NULL,
  `district` varchar(100) NOT NULL,
  `typical_temp_min` decimal(5,2) DEFAULT NULL,
  `typical_temp_max` decimal(5,2) DEFAULT NULL,
  `typical_humidity_min` decimal(5,2) DEFAULT NULL,
  `typical_humidity_max` decimal(5,2) DEFAULT NULL,
  `annual_rainfall_min` int(11) DEFAULT NULL,
  `annual_rainfall_max` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agro_ecological_zones`
--

INSERT INTO `agro_ecological_zones` (`id`, `zone_name`, `district`, `typical_temp_min`, `typical_temp_max`, `typical_humidity_min`, `typical_humidity_max`, `annual_rainfall_min`, `annual_rainfall_max`) VALUES
(1, 'Dry Zone', 'Anuradhapura', 20.00, 38.00, 50.00, 75.00, 625, 1900),
(2, 'Dry Zone', 'Polonnaruwa', 20.00, 38.00, 50.00, 75.00, 625, 1900),
(3, 'Dry Zone', 'Hambantota', 20.00, 38.00, 50.00, 75.00, 625, 1900),
(4, 'Dry Zone', 'Batticaloa', 20.00, 38.00, 50.00, 75.00, 625, 1900),
(5, 'Dry Zone', 'Jaffna', 20.00, 38.00, 50.00, 75.00, 625, 1900),
(6, 'Dry Zone', 'Puttalam', 20.00, 38.00, 50.00, 75.00, 625, 1900),
(7, 'Dry Zone', 'Mannar', 20.00, 38.00, 50.00, 75.00, 625, 1900),
(8, 'Dry Zone', 'Trincomalee', 20.00, 38.00, 50.00, 75.00, 625, 1900),
(9, 'Intermediate Zone', 'Kandy', 18.00, 30.00, 60.00, 85.00, 1500, 2500),
(10, 'Intermediate Zone', 'Badulla', 18.00, 30.00, 60.00, 85.00, 1500, 2500),
(11, 'Intermediate Zone', 'Kurunegala', 18.00, 30.00, 60.00, 85.00, 1500, 2500),
(12, 'Intermediate Zone', 'Matale', 18.00, 30.00, 60.00, 85.00, 1500, 2500),
(13, 'Wet Zone', 'Colombo', 24.00, 31.00, 70.00, 90.00, 2000, 3500),
(14, 'Wet Zone', 'Gampaha', 24.00, 31.00, 70.00, 90.00, 2000, 3500),
(15, 'Wet Zone', 'Kegalle', 24.00, 31.00, 70.00, 90.00, 2000, 3500),
(16, 'Wet Zone', 'Kalutara', 24.00, 31.00, 70.00, 90.00, 2000, 3500),
(17, 'Wet Zone', 'Ratnapura', 24.00, 31.00, 70.00, 90.00, 2000, 3500),
(18, 'Wet Zone', 'Galle', 24.00, 31.00, 70.00, 90.00, 2000, 3500),
(19, 'Wet Zone', 'Matara', 24.00, 31.00, 70.00, 90.00, 2000, 3500),
(20, 'Upcountry', 'Nuwara Eliya', 10.00, 20.00, 70.00, 90.00, 1500, 3000),
(21, 'Upcountry', 'Bandarawela', 10.00, 22.00, 70.00, 90.00, 1500, 3000);

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_reference` varchar(50) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','confirmed','processing','delivered') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_purchase` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plant_growing_advice`
--

CREATE TABLE `plant_growing_advice` (
  `id` int(11) NOT NULL,
  `plant_id` int(11) NOT NULL,
  `advice_type` enum('planting','watering','fertilizing','pest_control','harvesting') DEFAULT 'planting',
  `title` varchar(200) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plant_recommendations`
--

CREATE TABLE `plant_recommendations` (
  `id` int(11) NOT NULL,
  `plant_name` varchar(100) NOT NULL,
  `min_rainfall_mm` int(11) NOT NULL,
  `max_rainfall_mm` int(11) NOT NULL,
  `min_humidity_pct` decimal(5,2) NOT NULL,
  `max_humidity_pct` decimal(5,2) NOT NULL,
  `min_temp_c` decimal(5,2) NOT NULL,
  `max_temp_c` decimal(5,2) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plant_recommendations`
--

INSERT INTO `plant_recommendations` (`id`, `plant_name`, `min_rainfall_mm`, `max_rainfall_mm`, `min_humidity_pct`, `max_humidity_pct`, `min_temp_c`, `max_temp_c`, `description`) VALUES
(14, 'Tomato', 1000, 2000, 60.00, 80.00, 18.00, 28.00, 'Popular greenhouse vegetable.'),
(15, 'Cucumber', 1000, 2000, 60.00, 80.00, 24.00, 30.00, 'Suitable for Dry Zone with irrigation.'),
(16, 'Chilli', 600, 1000, 50.00, 75.00, 21.00, 27.00, 'Recommended for Dry Zone.'),
(17, 'Onions', 600, 1000, 50.00, 75.00, 20.00, 28.00, 'Can be grown in Dry Zone.'),
(18, 'Mango', 800, 2000, 55.00, 80.00, 22.00, 35.00, 'Suitable for Dry and Intermediate Zones.'),
(19, 'Banana', 1000, 2500, 60.00, 85.00, 22.00, 32.00, 'Suitable for Wet and Dry Zones.'),
(20, 'Pomegranate', 600, 1500, 50.00, 75.00, 20.00, 35.00, 'Suitable for Dry and Intermediate Zones.'),
(21, 'Pineapple', 1000, 2000, 60.00, 85.00, 22.00, 32.00, 'Can be cultivated in Dry Zone.'),
(22, 'Rambutan', 1500, 3000, 65.00, 85.00, 22.00, 32.00, 'Suitable for Wet Zone.'),
(23, 'Durian', 1500, 3000, 65.00, 85.00, 22.00, 32.00, 'Suitable for Wet Zone.'),
(24, 'Cinnamon', 1875, 2500, 75.00, 85.00, 25.00, 35.00, 'Thrives in Wet Zone.'),
(25, 'Potato', 1000, 2000, 60.00, 80.00, 15.00, 25.00, 'Suitable for Upcountry areas.'),
(26, 'Green Gram', 600, 1000, 50.00, 75.00, 22.00, 32.00, 'Suitable for Dry Zone uplands.');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` enum('vegetable','fruit','plant') NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `store_id`, `name`, `description`, `price`, `category`, `quantity`, `image`, `created_at`) VALUES
(1, 1, 'Organic Tomato', 'Juicy, vine-ripened organic tomatoes.', 4.50, 'vegetable', 100, 'tomato.jpg', '2026-09-01 20:54:48'),
(2, 1, 'Lavender Plant', 'Beautiful purple lavender, great for gardens.', 12.00, 'plant', 50, 'lavender.jpg', '2026-09-01 20:54:48'),
(3, 2, 'Strawberries', 'Sweet, fresh California strawberries.', 6.00, 'fruit', 80, 'strawberries.jpg', '2026-09-01 20:54:48'),
(4, 2, 'Basil Plant', 'Fresh aromatic basil for cooking.', 5.50, 'plant', 60, 'basil.jpg', '2026-09-01 20:54:48');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `is_hidden` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

CREATE TABLE `stores` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `store_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `city` varchar(50) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stores`
--

INSERT INTO `stores` (`id`, `owner_id`, `store_name`, `description`, `city`, `image`, `created_at`) VALUES
(1, 2, 'Green Thumb Nursery', 'Specializing in organic vegetables and rare plants.', 'Los Angeles', NULL, '2026-09-01 20:54:48'),
(2, 3, 'Fresh Harvest Greenhouse', 'Locally grown fruits and seasonal veggies.', 'San Francisco', NULL, '2026-09-01 20:54:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','owner','customer') NOT NULL DEFAULT 'customer',
  `city` varchar(50) NOT NULL,
  `address` text DEFAULT NULL,
  `gap_certificate` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `city`, `address`, `gap_certificate`, `created_at`) VALUES
(1, 'Super Admin', 'admin@crops.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'New York', 'Admin HQ', NULL, '2026-09-01 20:54:48'),
(2, 'Green Thumb Owner', 'owner1@crops.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', 'Los Angeles', '123 Farm Lane, LA', 'gap_cert_owner1.pdf', '2026-09-01 20:54:48'),
(3, 'Fresh Harvest Owner', 'owner2@crops.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', 'San Francisco', '456 Green Street, SF', 'gap_cert_owner2.pdf', '2026-09-01 20:54:48'),
(4, 'John Buyer', 'customer1@crops.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Los Angeles', '789 Home Ave, LA', NULL, '2026-09-01 20:54:48'),
(5, 'Jane Shopper', 'customer2@crops.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'San Diego', '101 Buyer Street, SD', NULL, '2026-09-01 20:54:48');

-- --------------------------------------------------------

--
-- Table structure for table `user_recommendations`
--

CREATE TABLE `user_recommendations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plant_id` int(11) NOT NULL,
  `temperature` decimal(5,2) DEFAULT NULL,
  `humidity` decimal(5,2) DEFAULT NULL,
  `match_percentage` int(11) DEFAULT NULL,
  `recommended_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_saved` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_warnings`
--
ALTER TABLE `admin_warnings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `agro_ecological_zones`
--
ALTER TABLE `agro_ecological_zones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `district` (`district`),
  ADD KEY `idx_district` (`district`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_reference` (`order_reference`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `store_id` (`store_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `plant_growing_advice`
--
ALTER TABLE `plant_growing_advice`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plant_id` (`plant_id`);

--
-- Indexes for table `plant_recommendations`
--
ALTER TABLE `plant_recommendations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `store_id` (`store_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `store_id` (`store_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_recommendations`
--
ALTER TABLE `user_recommendations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `plant_id` (`plant_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_warnings`
--
ALTER TABLE `admin_warnings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `agro_ecological_zones`
--
ALTER TABLE `agro_ecological_zones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `plant_growing_advice`
--
ALTER TABLE `plant_growing_advice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `plant_recommendations`
--
ALTER TABLE `plant_recommendations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stores`
--
ALTER TABLE `stores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_recommendations`
--
ALTER TABLE `user_recommendations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_warnings`
--
ALTER TABLE `admin_warnings`
  ADD CONSTRAINT `admin_warnings_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admin_warnings_ibfk_2` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `plant_growing_advice`
--
ALTER TABLE `plant_growing_advice`
  ADD CONSTRAINT `plant_growing_advice_ibfk_1` FOREIGN KEY (`plant_id`) REFERENCES `plant_recommendations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stores`
--
ALTER TABLE `stores`
  ADD CONSTRAINT `stores_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_recommendations`
--
ALTER TABLE `user_recommendations`
  ADD CONSTRAINT `user_recommendations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_recommendations_ibfk_2` FOREIGN KEY (`plant_id`) REFERENCES `plant_recommendations` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
