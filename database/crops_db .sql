-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 22, 2026 at 01:00 AM
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
-- Table structure for table `gap_certificates`
--

CREATE TABLE `gap_certificates` (
  `id` int(11) NOT NULL,
  `gap_number` varchar(50) NOT NULL,
  `holder_name` varchar(100) NOT NULL,
  `issued_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` enum('valid','expired','revoked') DEFAULT 'valid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gap_certificates`
--

INSERT INTO `gap_certificates` (`id`, `gap_number`, `holder_name`, `issued_date`, `expiry_date`, `status`, `created_at`) VALUES
(1, '261152319104756', 'Nadeesha Perera', '2026-06-02', '2029-06-01', 'valid', '2026-06-23 06:50:19'),
(2, '261152319204753', 'Kasun Wickramasinghe', '2026-06-02', '2027-06-01', 'valid', '2026-06-23 06:50:19'),
(3, '261152317104759', 'Ishara Rajapaksha', '2026-06-02', '2029-06-01', 'valid', '2026-06-23 06:50:19'),
(4, '261152317204758', 'Tharindu Kodikara', '2026-06-02', '2027-06-01', 'valid', '2026-06-23 06:50:19'),
(5, '261152305204611', 'Dilshan Ekanayake', '2026-05-15', '2027-05-14', 'valid', '2026-06-23 06:50:19'),
(6, '261152319204686', 'Anusha Wijesinghe', '2026-05-25', '2027-05-24', 'valid', '2026-06-23 06:50:19'),
(7, '261152319204708', 'Ravindu Liyanage', '2026-05-25', '2027-05-24', 'valid', '2026-06-23 06:50:19'),
(8, '261152319204705', 'Chamodi Weerasinghe', '2026-05-25', '2027-05-24', 'valid', '2026-06-23 06:50:19'),
(9, '261152312104376', 'Sahan Mendis', '2026-05-13', '2029-05-15', 'valid', '2026-06-23 06:50:19'),
(10, '261152312104380', 'Hiruni Kumarasinghe', '2026-05-13', '2029-05-12', 'valid', '2026-06-23 06:50:19'),
(11, '261152312104378', 'Pasindu Perera', '2026-05-13', '2029-05-12', 'valid', '2026-06-23 06:50:19'),
(12, '261152305303250', 'Sewmini Wickramasinghe', '2026-05-15', '2027-05-14', 'valid', '2026-06-23 06:50:19'),
(13, '261152315104642', 'Lakshan Rajapaksha', '2026-05-15', '2029-05-14', 'valid', '2026-06-23 06:50:19'),
(14, '261152305304281', 'Vinuri Kodikara', '2026-05-15', '2027-05-14', 'valid', '2026-06-23 06:50:19'),
(15, '261152303304691', 'Yohan Ekanayake', '2026-05-08', '2027-05-07', 'valid', '2026-06-23 06:50:19'),
(16, '261152317104644', 'Senuri Wijesinghe', '2026-05-04', '2027-05-03', 'valid', '2026-06-23 06:50:19'),
(17, '261152323204634', 'Tharusha Liyanage', '2026-04-21', '2027-04-20', 'valid', '2026-06-23 06:50:19'),
(18, '261152305202643', 'Imesha Weerasinghe', '2026-04-21', '2027-04-20', 'valid', '2026-06-23 06:50:19'),
(19, '261152304104499', 'Dinesh Mendis', '2026-04-02', '2029-04-01', 'valid', '2026-06-23 06:50:19'),
(20, '261152310204631', 'Kavindi Kumarasinghe', '2026-04-21', '2027-04-20', 'valid', '2026-06-23 06:50:19'),
(21, '261152318101450', 'Nimal Perera', '2026-03-30', '2027-03-29', 'valid', '2026-06-23 06:50:19'),
(22, '261152315204651', 'Sanduni Wickramasinghe', '2026-03-12', '2027-03-11', 'valid', '2026-06-23 06:50:19'),
(23, '261152311204510', 'Ruwan Rajapaksha', '2026-01-01', '2026-12-31', 'valid', '2026-06-23 06:50:19'),
(24, '261152307103731', 'Dilini Kodikara', '2026-01-07', '2027-01-06', 'valid', '2026-06-23 06:50:19'),
(25, '261152314104551', 'Chathura Ekanayake', '2026-01-21', '2027-01-20', 'valid', '2026-06-23 06:50:19');

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
(4, 2, 'Basil Plant', 'Fresh aromatic basil for cooking.', 5.50, 'plant', 60, 'basil.jpg', '2026-09-01 20:54:48'),
(5, 1, 'Jasmine', 'Jasmine - a popular flowering plant. Support with a trellis as it grows; fragrant blooms appear at night.', 765.76, 'plant', 10, 'jasmine.jpg', '2026-09-20 17:40:10'),
(6, 1, 'Snake Plant', 'Snake Plant - a popular indoor plant. Extremely hardy; water sparingly, ideal for beginners.', 721.57, 'plant', 5, 'snake_plant.jpg', '2026-09-20 17:40:10'),
(7, 2, 'Bougainvillea', 'Bougainvillea - a popular flowering plant. Thrives on neglect; too much water reduces flowering.', 1092.59, 'plant', 40, 'bougainvillea.jpg', '2026-09-20 17:40:10'),
(8, 2, 'Rose', 'Rose - a popular flowering plant. Prune regularly and water at the base to avoid fungal issues.', 330.96, 'plant', 20, 'rose.jpg', '2026-09-20 17:40:10'),
(9, 1, 'Aloe Vera', 'Aloe Vera - a popular succulent plant. Allow soil to dry out completely between waterings.', 734.30, 'plant', 10, 'aloe_vera.jpg', '2026-09-20 17:40:10'),
(10, 1, 'Brinjal', 'Fresh brinjal grown in greenhouse conditions.', 80.56, 'vegetable', 30, 'brinjal.jpg', '2026-09-20 17:40:10'),
(11, 1, 'Carrot', 'Fresh carrot grown in greenhouse conditions.', 72.48, 'vegetable', 30, 'carrot.jpg', '2026-09-20 17:40:10'),
(12, 2, 'Cabbage', 'Fresh cabbage grown in greenhouse conditions.', 174.77, 'vegetable', 30, 'cabbage.jpg', '2026-09-20 17:40:10'),
(13, 2, 'Pineapple', 'Fresh pineapple grown in greenhouse conditions.', 92.71, 'fruit', 30, 'pineapple.jpg', '2026-09-20 17:40:10'),
(14, 2, 'Mango', 'Fresh mango grown in greenhouse conditions.', 207.87, 'fruit', 100, 'mango.jpg', '2026-09-20 17:40:10'),
(35, 5, 'Capsicum', 'Fresh capsicum grown in greenhouse conditions.', 570.72, 'vegetable', 30, 'capsicum.jpg', '2026-09-21 01:52:57'),
(36, 5, 'Snake Gourd', 'Fresh snake gourd grown in greenhouse conditions.', 367.90, 'vegetable', 20, 'snake_gourd.jpg', '2026-09-21 01:52:57'),
(37, 5, 'Rose', 'Rose - a popular flowering plant. Prune regularly and water at the base.', 330.96, 'plant', 20, 'rose.jpg', '2026-09-21 01:52:57'),
(38, 6, 'Pomegranate', 'Fresh pomegranate grown in greenhouse conditions.', 365.70, 'fruit', 50, 'pomegranate.jpg', '2026-09-21 01:52:57'),
(39, 6, 'Bitter Gourd', 'Fresh bitter gourd grown in greenhouse conditions.', 145.21, 'vegetable', 0, 'bitter_gourd.jpg', '2026-09-21 01:52:57'),
(40, 6, 'Hibiscus', 'Hibiscus - a popular flowering plant. Deadhead spent blooms.', 699.68, 'plant', 15, 'hibiscus.jpg', '2026-09-21 01:52:57'),
(41, 7, 'Rambutan', 'Fresh rambutan grown in greenhouse conditions.', 557.13, 'fruit', 5, 'rambutan.jpg', '2026-09-21 01:52:57'),
(42, 7, 'Mango', 'Fresh mango grown in greenhouse conditions.', 207.87, 'fruit', 100, 'mango.jpg', '2026-09-21 01:52:57'),
(43, 7, 'Jasmine', 'Jasmine - a popular flowering plant. Fragrant blooms appear at night.', 624.96, 'plant', 40, 'jasmine.jpg', '2026-09-21 01:52:57'),
(45, 5, 'Cabbage', 'Fresh cabbage grown in greenhouse conditions.', 174.77, 'vegetable', 25, 'cabbage.jpg', '2026-09-21 02:22:04'),
(51, 7, 'Snake Gourd', 'Fresh snake gourd grown in greenhouse conditions.', 361.68, 'vegetable', 30, 'snake_gourd.jpg', '2026-09-21 02:22:04'),
(52, 7, 'Bougainvillea', 'Bougainvillea - a popular flowering plant.', 997.73, 'plant', 40, 'bougainvillea.jpg', '2026-09-21 02:22:04'),
(59, 8, 'Passion Fruit', 'Fresh passion fruit grown in greenhouse conditions.', 326.68, 'fruit', 20, 'passion_fruit.jpg', '2026-09-21 02:22:34'),
(60, 8, 'Tomato', 'Fresh tomato grown in greenhouse conditions.', 80.56, 'vegetable', 30, 'tomato.jpg', '2026-09-21 02:22:34'),
(61, 8, 'Aloe Vera', 'Aloe Vera - a popular succulent plant.', 402.67, 'plant', 10, 'aloe_vera.jpg', '2026-09-21 02:22:34'),
(62, 9, 'Strawberry', 'Fresh strawberry grown in greenhouse conditions.', 250.00, 'fruit', 40, 'strawberries.jpg', '2026-09-21 02:22:34'),
(63, 9, 'Carrot', 'Fresh carrot grown in greenhouse conditions.', 72.48, 'vegetable', 50, 'carrot.jpg', '2026-09-21 02:22:34'),
(64, 9, 'Snake Plant', 'Snake Plant - a popular indoor plant.', 721.57, 'plant', 15, 'snake_plant.jpg', '2026-09-21 02:22:34');

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
  `status` enum('active','pending') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stores`
--

INSERT INTO `stores` (`id`, `owner_id`, `store_name`, `description`, `city`, `image`, `status`, `created_at`) VALUES
(1, 2, 'Perera Garden Fresh', 'Premium organic vegetables and rare plants from Hambantota.', 'Hambantota', 'store1.jpg', 'pending', '2026-09-01 20:54:48'),
(2, 3, 'Wickramasinghe Plant Co', 'Locally grown fruits and seasonal veggies from Rathnapura.', 'Rathnapura', 'store2.jpg', 'pending', '2026-09-01 20:54:48'),
(5, 6, 'Rajapaksha Crop Hub', 'Fresh crops and plants from Kurunegala.', 'Kurunegala', 'store3.jpg', 'pending', '2026-09-21 01:48:32'),
(6, 7, 'Kodikara Plant Co', 'Quality agro products from Kurunegala.', 'Kurunegala', 'store4.jpg', 'pending', '2026-09-21 01:48:32'),
(7, 8, 'Ekanayake Agro Farm', 'Organic farm fresh produce from Gampaha.', 'Gampaha', 'store5.jpg', 'pending', '2026-09-21 01:48:32'),
(8, 6, 'Wijesinghe Green Acres', 'Quality plants and vegetables from Rathnapura.', 'Rathnapura', 'store8.jpg', 'pending', '2026-09-21 01:58:40'),
(9, 7, 'Liyanage Organic Farm', 'Fresh organic produce from Rathnapura.', 'Rathnapura', 'store9.jpg', 'pending', '2026-09-21 01:58:40'),
(10, 8, 'Weerasinghe Garden Fresh', 'Premium greenhouse products from Rathnapura.', 'Rathnapura', 'store10.jpg', 'pending', '2026-09-20 20:28:40'),
(11, 3, 'Mendis Organic Farm', 'Fresh organic produce from Anuradhapura.', 'Anuradhapura', 'store6.jpg', 'pending', '2026-09-20 20:28:40'),
(12, 8, 'Ekanayake Harvest Farm', 'Fresh produce by GAP certified grower Dilshan Ekanayake.', 'Rathnapura', 'store10.jpg', 'pending', '2026-09-20 20:28:40'),
(13, 6, 'Rajapaksha Green Garden', 'Quality vegetables from GAP certified grower Ishara Rajapaksha.', 'Kurunegala', 'store11.jpg', 'pending', '2026-09-20 20:28:40');

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
  `gap_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `city`, `address`, `gap_certificate`, `gap_number`, `created_at`) VALUES
(1, 'Super Admin', 'admin@crops.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'New York', 'Admin HQ', NULL, NULL, '2026-09-01 20:54:48'),
(2, 'Nadeesha Perera', 'nadeesha.perera@crops.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', 'Hambantota', 'No.7, Hill View, Hambantota', 'gap_cert_owner1.pdf', NULL, '2026-09-01 20:54:48'),
(3, 'Kasun Wickramasinghe', 'kasun.wickramasinghe@crops.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', 'Rathnapura', 'No.23, New Town, Rathnapura', 'gap_cert_owner2.pdf', NULL, '2026-09-01 20:54:48'),
(4, 'Sahan Mendis', 'sahan.mendis@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Anuradhapura', 'No.50, Lake View, Anuradhapura', NULL, NULL, '2026-09-01 20:54:48'),
(5, 'Hiruni Kumarasinghe', 'hiruni.kumarasinghe@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Colombo', 'No.60, Lake View, Colombo', NULL, NULL, '2026-09-01 20:54:48'),
(6, 'Ishara Rajapaksha', 'ishara.rajapaksha@crops.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', 'Kurunegala', 'No.60, School Lane, Kurunegala', 'gap_cert_owner3.pdf', NULL, '2026-09-21 01:48:32'),
(7, 'Tharindu Kodikara', 'tharindu.kodikara@crops.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', 'Kurunegala', 'No.108, Garden Lane, Kurunegala', 'gap_cert_owner4.pdf', NULL, '2026-09-21 01:48:32'),
(8, 'Dilshan Ekanayake', 'dilshan.ekanayake@crops.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', 'Gampaha', 'No.179, Park Road, Gampaha', 'gap_cert_owner5.pdf', NULL, '2026-09-21 01:48:32'),
(9, 'Anusha Wijesinghe', 'anusha.wijesinghe@crops.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', 'Rathnapura', 'No.27, Lake View, Rathnapura', 'gap_cert_owner6.pdf', NULL, '2026-09-21 02:28:04'),
(10, 'Ravindu Liyanage', 'ravindu.liyanage@crops.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', 'Rathnapura', 'No.68, Temple Road, Rathnapura', 'gap_cert_owner7.pdf', NULL, '2026-09-21 02:28:04'),
(11, 'Chamodi Weerasinghe', 'chamodi.weerasinghe@crops.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', 'Rathnapura', 'No.142, Hill View, Rathnapura', 'gap_cert_owner8.pdf', NULL, '2026-09-21 02:28:04'),
(12, 'Perera Owner', 'pereragreenacres10@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', 'Anuradhapura', 'No.42, River Side, Anuradhapura', NULL, NULL, '2026-06-23 06:50:19'),
(13, 'Wickramasinghe Owner', 'wickramasinghegardenfresh11@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', 'Gampaha', 'No.156, Main Street, Gampaha', NULL, NULL, '2026-06-23 06:50:19');

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
-- Indexes for table `gap_certificates`
--
ALTER TABLE `gap_certificates`
  ADD PRIMARY KEY (`id`);

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
  ADD UNIQUE KEY `uq_store_product` (`store_id`,`name`),
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
-- AUTO_INCREMENT for table `gap_certificates`
--
ALTER TABLE `gap_certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stores`
--
ALTER TABLE `stores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
