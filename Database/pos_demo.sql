-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 09, 2025 at 03:59 PM
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
-- Database: `pos_demo`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(12, 'Accessories'),
(1, 'Electronics'),
(16, 'Tech');

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `loyalty_points` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `loyalty_points` int(11) DEFAULT 0,
  `username` varchar(50) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `phone`, `email`, `loyalty_points`, `username`, `first_name`, `last_name`, `address`, `created_at`) VALUES
(1, 'John Doe', '1234567890', 'john@example.com', 0, '', NULL, NULL, NULL, '2025-06-05 00:48:24'),
(2, '', '123-456-7890', 'john@example.com', 0, 'john_doe', 'John', 'Doe', '123 Main St', '2025-06-05 00:48:24');

-- --------------------------------------------------------

--
-- Table structure for table `discounts`
--

CREATE TABLE `discounts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('percentage','fixed') NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `min_purchase_amount` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `discounts`
--

INSERT INTO `discounts` (`id`, `name`, `type`, `value`, `product_id`, `category_id`, `start_date`, `end_date`, `created_at`, `min_purchase_amount`, `is_active`) VALUES
(7, 'Weekly Sale', 'percentage', 10.00, 9, 12, '2025-06-10 16:43:00', '2025-06-17 16:43:00', '2025-06-09 11:13:25', 300.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `generated_at` datetime NOT NULL,
  `generated_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `sale_id`, `invoice_number`, `file_path`, `generated_at`, `generated_by`) VALUES
(1, 28, 'INV-20250604-000028', 'Invoices/INV-20250604-000028.pdf', '2025-06-04 03:32:30', 2),
(2, 30, 'INV-20250604-000030', 'Invoices/INV-20250604-000030.pdf', '2025-06-04 03:41:41', 2),
(3, 38, 'INV-20250604-000038', 'Invoices/INV-20250604-000038.pdf', '2025-06-04 09:09:52', 2);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `quantity`, `image`, `description`, `barcode`, `category_id`, `stock_quantity`) VALUES
(8, 'Tablet', 289.99, 0, NULL, '', 'BAR55', 1, 0),
(9, 'Headphones', 99.99, 27, NULL, 'Description for Headphones', 'BAR9', 1, 0),
(10, 'Laptop', 3999.00, 0, NULL, 'Description for Laptop', 'BAR10', 1, 0),
(11, 'Mouse', 50.00, 0, NULL, 'Description for Mouse', 'BAR11', 1, 0),
(13, 'Laptop', 4555.00, 0, NULL, 'Description for Laptop', 'BAR13', 1, 0),
(14, 'keyboard', 45.00, 4, NULL, 'Description for keyboard', 'BAR14', 1, 0),
(15, 'card', 50.00, 1, 'product_15_1748965730.jpeg', 'Description for card', 'BAR15', 1, 0),
(16, 'Batteries', 200.00, 2, NULL, '', 'BAR16', 1, 0),
(17, 'Laptop', 1000.00, 1, NULL, 'Gaming laptop', '1234567890', 1, 0),
(20, 'Headphones', 50.00, 1, NULL, 'Wireless headphones', '4567890123', 1, 0),
(23, 'Charger', 150.00, 19, NULL, NULL, 'BAR30', 1, 0),
(31, 'Sample Product', 10.00, 0, NULL, NULL, NULL, NULL, 100),
(32, 'Sample Product', 10.00, 0, NULL, NULL, NULL, NULL, 100),
(33, 'Sample Product', 10.00, 0, NULL, NULL, NULL, NULL, 120),
(37, 'Charger', 100.00, 10, NULL, '', 'BAR01', 12, 0),
(38, 'screen', 300.00, 10, NULL, NULL, 'BAR00', 12, 0);

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `discount_id` int(11) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `sale_date` datetime NOT NULL DEFAULT current_timestamp(),
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_details`
--

CREATE TABLE `sales_details` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_header`
--

CREATE TABLE `sales_header` (
  `id` int(11) NOT NULL,
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `user_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `order_status` varchar(50) NOT NULL DEFAULT 'pending',
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT NULL,
  `final_amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_header`
--

INSERT INTO `sales_header` (`id`, `sale_date`, `total_amount`, `user_id`, `customer_id`, `order_status`, `subtotal`, `discount`, `tax`, `discount_amount`, `final_amount`) VALUES
(1, '2025-06-03 14:45:07', 1000.00, 1, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(2, '2025-06-03 14:57:40', 500.00, 1, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(3, '2025-06-03 15:40:17', 100.00, 1, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(4, '2025-06-03 15:58:56', 800.00, 1, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(8, '2025-06-03 14:45:07', 1000.00, 1, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(9, '2025-06-03 14:57:40', 500.00, 1, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(10, '2025-06-03 15:40:17', 100.00, 1, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(11, '2025-06-03 15:58:56', 800.00, 1, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(15, '2025-06-03 14:45:07', 1000.00, 1, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(16, '2025-06-03 14:57:40', 500.00, 1, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(17, '2025-06-03 15:40:17', 100.00, 1, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(18, '2025-06-03 15:58:56', 800.00, 1, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(24, '2025-06-03 19:46:03', 1000.00, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(25, '2025-06-03 20:42:03', 300.00, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(26, '2025-06-03 20:51:35', 199.98, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(27, '2025-06-03 20:51:51', 999.90, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(28, '2025-06-03 20:52:02', 999.90, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(29, '2025-06-03 22:10:20', 19995.00, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(30, '2025-06-03 22:10:47', 4555.00, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(31, '2025-06-04 00:32:22', 4555.00, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(32, '2025-06-04 00:35:46', 200.00, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(33, '2025-06-04 00:37:26', 200.00, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(34, '2025-06-04 00:39:34', 350.00, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(35, '2025-06-04 00:40:54', 150.00, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(36, '2025-06-04 00:43:49', 2999.90, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(37, '2025-06-04 01:11:16', 50.00, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(38, '2025-06-04 01:11:25', 99.99, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(39, '2025-06-04 12:16:18', 50.00, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(43, '2025-06-04 13:31:25', 4555.00, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(44, '2025-06-04 14:04:15', 2000.00, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(45, '2025-06-04 14:05:01', 150.00, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(46, '2025-06-04 14:05:29', 50.00, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(47, '2025-06-04 14:21:15', 899.97, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(48, '2025-06-04 14:42:14', 299.99, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(49, '2025-06-04 14:46:21', 50.00, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(50, '2025-06-04 14:47:31', 299.99, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(51, '2025-06-04 14:59:29', 50.00, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(52, '2025-06-04 15:03:35', 50.00, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(53, '2025-06-04 15:04:24', 50.00, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(54, '2025-06-04 16:04:17', 50.00, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(55, '2025-06-04 17:25:54', 50.00, 2, NULL, 'pending', 0.00, 0.00, 0.00, NULL, NULL),
(56, '2025-06-05 04:30:00', 100.50, 1, 1, 'completed', 0.00, 0.00, 0.00, NULL, NULL),
(63, '2025-06-06 17:37:06', 157.50, 2, NULL, 'pending', 150.00, 0.00, 7.50, NULL, NULL),
(64, '2025-06-09 11:09:04', 4555.00, 2, NULL, 'pending', 0.00, 0.00, 0.00, 0.00, 4555.00);

-- --------------------------------------------------------

--
-- Table structure for table `sale_discounts`
--

CREATE TABLE `sale_discounts` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `discount_id` int(11) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `sale_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`id`, `product_id`, `quantity`, `sale_date`, `sale_id`, `price`, `discount`, `subtotal`, `tax`, `discount_amount`) VALUES
(4, 11, 2, '2025-06-03 15:40:17', 3, 50.00, 0.00, 0.00, 0.00, NULL),
(10, 9, 2, '2025-06-03 20:51:35', 26, 99.99, 0.00, 0.00, 0.00, NULL),
(11, 9, 10, '2025-06-03 20:51:51', 27, 99.99, 0.00, 0.00, 0.00, NULL),
(12, 9, 10, '2025-06-03 20:52:02', 28, 99.99, 0.00, 0.00, 0.00, NULL),
(13, 10, 5, '2025-06-03 22:10:20', 29, 3999.00, 0.00, 0.00, 0.00, NULL),
(14, 13, 1, '2025-06-03 22:10:47', 30, 4555.00, 0.00, 0.00, 0.00, NULL),
(15, 13, 1, '2025-06-04 00:32:22', 31, 4555.00, 0.00, 0.00, 0.00, NULL),
(16, 16, 1, '2025-06-04 00:35:46', 32, 200.00, 0.00, 0.00, 0.00, NULL),
(17, 16, 1, '2025-06-04 00:37:26', 33, 200.00, 0.00, 0.00, 0.00, NULL),
(18, 15, 7, '2025-06-04 00:39:34', 34, 50.00, 0.00, 0.00, 0.00, NULL),
(19, 11, 3, '2025-06-04 00:40:54', 35, 50.00, 0.00, 0.00, 0.00, NULL),
(20, 8, 10, '2025-06-04 00:43:49', 36, 299.99, 0.00, 0.00, 0.00, NULL),
(21, 15, 1, '2025-06-04 01:11:16', 37, 50.00, 0.00, 0.00, 0.00, NULL),
(22, 9, 1, '2025-06-04 01:11:25', 38, 99.99, 0.00, 0.00, 0.00, NULL),
(23, 15, 1, '2025-06-04 12:16:18', 39, 50.00, 0.00, 0.00, 0.00, NULL),
(24, 13, 1, '2025-06-04 13:31:25', 43, 4555.00, 0.00, 0.00, 0.00, NULL),
(25, 17, 2, '2025-06-04 14:04:15', 44, 1000.00, 0.00, 0.00, 0.00, NULL),
(26, 11, 3, '2025-06-04 14:05:01', 45, 50.00, 0.00, 0.00, 0.00, NULL),
(27, 11, 1, '2025-06-04 14:05:29', 46, 50.00, 0.00, 0.00, 0.00, NULL),
(28, 8, 3, '2025-06-04 14:21:15', 47, 299.99, 0.00, 0.00, 0.00, NULL),
(29, 8, 1, '2025-06-04 14:42:14', 48, 299.99, 0.00, 0.00, 0.00, NULL),
(30, 11, 1, '2025-06-04 14:46:21', 49, 50.00, 0.00, 0.00, 0.00, NULL),
(31, 8, 1, '2025-06-04 14:47:31', 50, 299.99, 0.00, 0.00, 0.00, NULL),
(32, 11, 1, '2025-06-04 14:59:29', 51, 50.00, 0.00, 0.00, 0.00, NULL),
(33, 11, 1, '2025-06-04 15:03:35', 52, 50.00, 0.00, 0.00, 0.00, NULL),
(34, 11, 1, '2025-06-04 15:04:24', 53, 50.00, 0.00, 0.00, 0.00, NULL),
(35, 11, 1, '2025-06-04 16:04:17', 54, 50.00, 0.00, 0.00, 0.00, NULL),
(36, 11, 1, '2025-06-04 17:25:54', 55, 50.00, 0.00, 0.00, 0.00, NULL),
(41, 23, 1, '2025-06-06 17:37:06', 63, 150.00, 0.00, 0.00, 0.00, NULL),
(42, 13, 1, '2025-06-09 11:09:04', 64, 4555.00, 0.00, 0.00, 0.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `value` varchar(255) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `name`, `value`, `updated_at`) VALUES
(1, 'low_stock_threshold', '20', '2025-06-03 21:39:15');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `token_expires_at` datetime DEFAULT NULL,
  `role` enum('admin','client') NOT NULL DEFAULT 'client'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `remember_token`, `token_expires_at`, `role`) VALUES
(1, 'admin', NULL, '$2y$10$jemyJwX.FRlPiVG6ZwreBuav.fltHZZT2FC4xbNFfNZxyqnwBoFIi', NULL, NULL, 'admin'),
(2, 'client1', 'client1@example.com', '$2y$10$Z.hXQG0PQ2NDYxOZVX3IxOazQUvmujASFPezekwqqoSSWpnCw8NW.', NULL, NULL, 'client'),
(5, 'admin1', 'admin@example.com', '$2y$10$zMm9MsqizB/7TguXe4tjmO3dt2E11WXxFsFYdFYj1Y735ZI4M0GLO', NULL, NULL, 'admin'),
(9, 'cashier1', NULL, 'cashier123', NULL, NULL, ''),
(10, 'manager1', NULL, 'manager123', NULL, NULL, ''),
(11, 'admin_user', 'admin@example.com', 'hashed_password', NULL, NULL, 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_name` (`name`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_username` (`username`);

--
-- Indexes for table `discounts`
--
ALTER TABLE `discounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_validity` (`start_date`,`end_date`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `generated_by` (`generated_by`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `idx_products_barcode` (`barcode`),
  ADD KEY `idx_products_quantity` (`quantity`),
  ADD KEY `fk_category_id` (`category_id`);

--
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `discount_id` (`discount_id`);

--
-- Indexes for table `sales_details`
--
ALTER TABLE `sales_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `sales_header`
--
ALTER TABLE `sales_header`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sales_header_user_id` (`user_id`),
  ADD KEY `fk_customer_id` (`customer_id`);

--
-- Indexes for table `sale_discounts`
--
ALTER TABLE `sale_discounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `discount_id` (`discount_id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_sale_items_sale_date` (`sale_date`),
  ADD KEY `idx_sale_items_sale_id` (`sale_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `discounts`
--
ALTER TABLE `discounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_details`
--
ALTER TABLE `sales_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_header`
--
ALTER TABLE `sales_header`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `sale_discounts`
--
ALTER TABLE `sale_discounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2604;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `discounts`
--
ALTER TABLE `discounts`
  ADD CONSTRAINT `discounts_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `discounts_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales_header` (`id`),
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_category_id` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `sales_ibfk_3` FOREIGN KEY (`discount_id`) REFERENCES `discounts` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sales_details`
--
ALTER TABLE `sales_details`
  ADD CONSTRAINT `sales_details_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales_header` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales_header`
--
ALTER TABLE `sales_header`
  ADD CONSTRAINT `fk_customer_id` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_header_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `sales_header_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sale_discounts`
--
ALTER TABLE `sale_discounts`
  ADD CONSTRAINT `sale_discounts_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales_header` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sale_discounts_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sale_discounts_ibfk_3` FOREIGN KEY (`discount_id`) REFERENCES `discounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `sale_items_ibfk_3` FOREIGN KEY (`sale_id`) REFERENCES `sales_header` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
