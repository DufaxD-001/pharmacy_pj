-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 02, 2026 at 04:21 AM
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
-- Database: `pharmacy_pj`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'cash',
  `payment_status` enum('Pending','Paid','Failed') DEFAULT 'Pending',
  `reference` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `invoice_no` varchar(50) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivery_type` enum('pickup','delivery') DEFAULT 'pickup',
  `delivery_address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_price`, `payment_method`, `payment_status`, `reference`, `created_at`, `invoice_no`, `order_date`, `delivery_type`, `delivery_address`) VALUES
(8, 11, 7000.00, 'cash', 'Paid', 'kwl0rjgpze', '2026-05-07 03:23:16', 'PH-2026-5119', '2026-05-07 03:23:16', 'pickup', NULL),
(9, 11, 3700.00, 'cash', 'Paid', '4ypmab78nc', '2026-05-07 03:26:40', 'PH-2026-3400', '2026-05-07 03:26:40', 'pickup', NULL),
(10, 11, 5500.00, 'cash', 'Paid', 'x97aezh3mj', '2026-05-07 03:34:28', 'PH-2026-7588', '2026-05-07 03:34:28', 'pickup', NULL),
(11, 11, 1000.00, 'cash', 'Paid', '7hs0i48nyz', '2026-05-07 03:43:55', 'PH-2026-5976', '2026-05-07 03:43:55', 'pickup', NULL),
(12, 11, 1200.00, 'cash', 'Paid', '32ngey9hjd', '2026-05-07 04:27:45', 'PH-2026-0523', '2026-05-07 04:27:45', 'pickup', NULL),
(13, 11, 22400.00, 'cash', 'Paid', 'm8zhuqb124', '2026-05-07 18:24:37', 'PH-2026-3120', '2026-05-07 18:24:37', 'pickup', NULL),
(20, 5, 4500.00, 'cash', 'Paid', '6q4x27swre', '2026-05-10 23:15:46', 'PH-2026-5478', '2026-05-10 23:15:46', 'pickup', NULL),
(21, 5, 2500.00, 'cash', 'Paid', 'm0u789qkec', '2026-05-10 23:16:08', 'PH-2026-8989', '2026-05-10 23:16:08', 'pickup', NULL),
(22, 5, 4500.00, 'cash', 'Paid', 'o9dtc7as4j', '2026-05-10 23:43:13', 'PH-2026-5344', '2026-05-10 23:43:13', 'pickup', NULL),
(23, 5, 2500.00, 'cash', 'Paid', 'yq9xac0nm6', '2026-05-10 23:44:10', 'PH-2026-7700', '2026-05-10 23:44:10', 'pickup', NULL),
(24, 5, 2500.00, 'cash', 'Paid', '5useg8c71q', '2026-05-12 03:43:11', 'PH-2026-2463', '2026-05-12 03:43:11', 'pickup', NULL),
(25, 11, 2000.00, 'cash', 'Paid', 'noqly7gda8', '2026-05-15 00:03:42', 'PH-2026-1343', '2026-05-15 00:03:42', 'pickup', NULL),
(26, 11, 2500.00, 'cash', 'Paid', 'f43yta9sw5', '2026-05-15 00:06:16', 'PH-2026-7612', '2026-05-15 00:06:16', 'pickup', NULL),
(27, 11, 2500.00, 'cash', 'Paid', '3iynm6kzaf', '2026-05-15 00:17:39', 'PH-2026-8981', '2026-05-15 00:17:39', 'delivery', 'no 18 kenneth fomah street,ori-okuta,ikorodu,lagos.'),
(28, 5, 1200.00, 'cash', 'Paid', 'vjncy5rau2', '2026-05-16 00:30:03', 'PH-2026-2920', '2026-05-16 00:30:03', 'pickup', ''),
(29, 5, 5000.00, 'cash', 'Paid', '4olxm56bj2', '2026-05-16 00:47:09', 'PH-2026-8580', '2026-05-16 00:47:09', 'pickup', ''),
(30, 5, 5200.00, 'cash', 'Paid', '8g9jswct3l', '2026-05-16 01:09:00', 'PH-2026-9273', '2026-05-16 01:09:00', 'pickup', ''),
(31, 5, 2500.00, 'cash', 'Paid', 'wjxzi98b40', '2026-05-16 01:23:45', 'PH-2026-7329', '2026-05-16 01:23:45', 'pickup', ''),
(32, 5, 2000.00, 'cash', 'Paid', 'TXN-OLA2V07H', '2026-05-16 03:33:52', 'INV-20260516-950', '2026-05-16 03:33:52', 'pickup', NULL),
(33, 11, 2500.00, 'paystack', 'Paid', 'bdkfyo07zg', '2026-05-16 03:46:05', 'PH-2026-2439', '2026-05-16 03:46:05', 'delivery', 'gra,ilorin'),
(34, 5, 3200.00, 'cash', 'Paid', 'TXN-BEH2LKMZ', '2026-05-20 04:30:10', 'INV-20260520-432', '2026-05-20 04:30:10', 'pickup', NULL),
(35, 5, 2000.00, 'cash', 'Paid', 'TXN-LH2G567A', '2026-05-20 05:17:38', 'INV-20260520-289', '2026-05-20 05:17:38', 'pickup', NULL),
(36, 11, 1200.00, 'paystack', 'Paid', 'gi72l8jb04', '2026-05-22 00:30:48', 'PH-2026-0607', '2026-05-22 00:30:48', 'pickup', ''),
(37, 5, 2400.00, 'pos', 'Paid', 'TXN-L9VTOKC3', '2026-05-22 01:10:20', 'INV-20260522-167', '2026-05-22 01:10:20', 'pickup', NULL),
(38, 5, 5400.00, 'cash', 'Paid', 'TXN-1XK2ZI0M', '2026-05-31 15:56:13', 'INV-20260531-456', '2026-05-31 15:56:13', 'pickup', NULL),
(39, 16, 3000.00, 'paystack', 'Paid', 'aium87ftsk', '2026-05-31 16:03:29', 'PH-2026-2020', '2026-05-31 16:03:29', 'delivery', 'fare 2 ilorin'),
(40, 16, 2700.00, 'paystack', 'Paid', '2etzry0x36', '2026-05-31 16:17:49', 'PH-2026-8866', '2026-05-31 16:17:49', 'pickup', '');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `unit_price`) VALUES
(8, 12, 2, 1, 1200.00),
(9, 13, 2, 2, 1200.00),
(10, 13, 4, 4, 5000.00),
(17, 20, 3, 1, 2500.00),
(18, 20, 5, 1, 2000.00),
(19, 21, 3, 1, 2500.00),
(20, 22, 3, 1, 2500.00),
(21, 22, 5, 1, 2000.00),
(22, 23, 3, 1, 2500.00),
(23, 24, 3, 1, 2500.00),
(24, 25, 5, 1, 2000.00),
(25, 26, 5, 1, 2000.00),
(26, 27, 5, 1, 2000.00),
(27, 28, 2, 1, 1200.00),
(28, 29, 3, 1, 2500.00),
(29, 30, 5, 1, 2000.00),
(30, 30, 2, 1, 1200.00),
(31, 31, 3, 1, 2500.00),
(32, 32, 5, 1, 2000.00),
(33, 33, 5, 1, 2000.00),
(34, 34, 5, 1, 2000.00),
(35, 34, 2, 1, 1200.00),
(36, 35, 5, 1, 2000.00),
(37, 36, 2, 1, 1200.00),
(38, 37, 2, 2, 1200.00),
(39, 38, 3, 2, 2500.00),
(40, 38, 7, 2, 200.00),
(41, 39, 3, 1, 2500.00),
(42, 40, 3, 1, 2500.00),
(43, 40, 7, 1, 200.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_status` enum('In Stock','Out of Stock') DEFAULT 'In Stock',
  `description` text DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `barcode`, `category`, `price`, `stock_status`, `description`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 'Paracetamol 500mg', NULL, 'Pain Relief', 500.00, 'In Stock', 'Effective for fever and mild pain.', 15, '2026-04-22 23:48:37', '2026-05-31 15:52:06'),
(2, 'Vitamin C 1000mg', NULL, 'Supplements', 1200.00, 'In Stock', 'Immune system booster.', 12, '2026-04-22 23:48:37', '2026-05-22 01:10:20'),
(3, 'Amoxicillin', NULL, 'Antibiotics', 2500.00, 'Out of Stock', 'Prescription required.', 11, '2026-04-22 23:48:37', '2026-05-31 16:17:49'),
(4, 'Peniciin ointment', NULL, 'Pain Relief', 5000.00, 'Out of Stock', NULL, 0, '2026-04-22 23:48:37', '2026-05-07 18:24:37'),
(5, 'phenic', NULL, 'Pain Relief', 2000.00, 'Out of Stock', NULL, 0, '2026-04-30 03:27:25', '2026-05-20 05:17:38'),
(6, 'ibuprofen', NULL, 'Pain Relief', 2000.00, 'In Stock', NULL, 20, '2026-05-31 15:48:42', '2026-05-31 15:48:42'),
(7, 'flagyl', NULL, 'Antibiotics', 200.00, 'In Stock', NULL, 17, '2026-05-31 15:49:51', '2026-05-31 16:17:49'),
(8, 'vitamin d', NULL, 'Supplements', 800.00, 'In Stock', NULL, 12, '2026-05-31 15:50:30', '2026-05-31 15:50:30');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `sold_by` int(11) DEFAULT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `change_amount` decimal(10,2) DEFAULT NULL,
  `transaction_ref` varchar(100) DEFAULT NULL,
  `payment_method` varchar(20) DEFAULT NULL,
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `product_id`, `sold_by`, `sale_price`, `amount_paid`, `change_amount`, `transaction_ref`, `payment_method`, `sale_date`) VALUES
(1, 3, 5, 2500.00, 2500.00, 0.00, NULL, NULL, '2026-05-09 03:08:37'),
(2, 3, 5, 2500.00, 2500.00, 0.00, NULL, NULL, '2026-05-09 03:15:34'),
(3, 3, 5, 2500.00, 2500.00, 0.00, '6q4x27swre', 'Paystack', '2026-05-10 23:15:46'),
(4, 5, 5, 2000.00, 2000.00, 0.00, '6q4x27swre', 'Paystack', '2026-05-10 23:15:46'),
(5, 3, 5, 2500.00, 2500.00, 0.00, 'm0u789qkec', 'Paystack', '2026-05-10 23:16:08'),
(6, 3, 5, 2500.00, 2500.00, 0.00, 'o9dtc7as4j', 'Paystack', '2026-05-10 23:43:13'),
(7, 5, 5, 2000.00, 2000.00, 0.00, 'o9dtc7as4j', 'Paystack', '2026-05-10 23:43:13'),
(8, 3, 5, 2500.00, 2500.00, 0.00, 'yq9xac0nm6', 'Paystack', '2026-05-10 23:44:10'),
(9, 3, 5, 2500.00, 2500.00, 0.00, '5useg8c71q', 'Paystack', '2026-05-12 03:43:11'),
(10, 5, 11, 2000.00, 2000.00, 0.00, 'noqly7gda8', 'Paystack', '2026-05-15 00:03:42'),
(11, 5, 11, 2000.00, 2000.00, 0.00, 'f43yta9sw5', 'Paystack', '2026-05-15 00:06:16'),
(12, 5, 11, 2000.00, 2000.00, 0.00, '3iynm6kzaf', 'Paystack', '2026-05-15 00:17:39'),
(13, 2, 5, 1200.00, 1200.00, 0.00, 'vjncy5rau2', 'Paystack', '2026-05-16 00:30:03'),
(14, 3, 5, 2500.00, 2500.00, 0.00, '4olxm56bj2', 'Paystack', '2026-05-16 00:47:09'),
(15, 5, 5, 2000.00, 2000.00, 0.00, '8g9jswct3l', 'Paystack', '2026-05-16 01:09:00'),
(16, 2, 5, 1200.00, 1200.00, 0.00, '8g9jswct3l', 'Paystack', '2026-05-16 01:09:00'),
(17, 3, 5, 2500.00, 2500.00, 0.00, 'wjxzi98b40', 'Paystack', '2026-05-16 01:23:45'),
(18, 5, 11, 2000.00, 2000.00, 0.00, 'bdkfyo07zg', 'Paystack', '2026-05-16 03:46:05'),
(19, 5, 5, 2000.00, 2000.00, 0.00, 'TXN-LH2G567A', 'cash', '2026-05-20 05:17:38'),
(20, 2, 11, 1200.00, 1200.00, 0.00, 'gi72l8jb04', 'Paystack', '2026-05-22 00:30:48'),
(21, 2, 5, 1200.00, 2400.00, 0.00, 'TXN-L9VTOKC3', 'pos', '2026-05-22 01:10:20'),
(22, 3, 5, 2500.00, 5000.00, 0.00, 'TXN-1XK2ZI0M', 'cash', '2026-05-31 15:56:13'),
(23, 7, 5, 200.00, 400.00, 0.00, 'TXN-1XK2ZI0M', 'cash', '2026-05-31 15:56:13'),
(24, 3, 16, 2500.00, 2500.00, 0.00, 'aium87ftsk', 'Paystack', '2026-05-31 16:03:29'),
(25, 3, 16, 2500.00, 2500.00, 0.00, '2etzry0x36', 'Paystack', '2026-05-31 16:17:49'),
(26, 7, 16, 200.00, 200.00, 0.00, '2etzry0x36', 'Paystack', '2026-05-31 16:17:49');

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 3, 1, 2500.00, 2500.00),
(2, 2, 3, 1, 2500.00, 2500.00);

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `page_name` varchar(50) NOT NULL,
  `content` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `page_name`, `content`, `updated_at`) VALUES
(1, 'home', '50% discount available for paracetamol', '2026-04-23 00:21:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff','client') NOT NULL,
  `otp_code` varchar(10) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `status` varchar(20) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `otp_code`, `is_verified`, `status`) VALUES
(4, 'admin_fawaz', NULL, 'admin123', 'admin', NULL, 1, 'active'),
(5, 'staff_user', NULL, '$2y$12$RVRD9YIM73ltM7wAuGFF4.dIbpDtfjmt8HfQQk5OkBCbdJkoHy1Ra', 'staff', NULL, 1, 'active'),
(11, 'dammykay', 'badmusdamilola79@gmail.com', '$2y$10$pXfoi7SFq7eNlMiCD4x1OegNmazkrs1anaY7cI6C6pOWXsoWTy0gG', 'client', NULL, 1, 'active'),
(16, 'tylex', 'tylexyoung567@gmail.com', '$2y$10$fOjyZ4UDuhAgdqbsqFjspOkqrXWcEryxi6ux8MnMPjlPI.AcK9eyi', 'client', NULL, 1, 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_name` (`product_name`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_quantity` (`quantity`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `sold_by` (`sold_by`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`sold_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
