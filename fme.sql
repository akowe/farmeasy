-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 07, 2022 at 05:01 PM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 7.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fme`
--

-- --------------------------------------------------------

--
-- Table structure for table `country`
--

CREATE TABLE `country` (
  `id` int(10) UNSIGNED NOT NULL,
  `country_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `country`
--

INSERT INTO `country` (`id`, `country_code`, `country`, `created_at`, `updated_at`) VALUES
(1, '+234', 'Nigeria', '2022-09-06 21:39:06', '2022-09-06 21:39:06'),
(2, '+255', 'Tanzania', '2022-09-06 21:39:06', '2022-09-06 21:39:06');

-- --------------------------------------------------------

--
-- Table structure for table `farm_type`
--

CREATE TABLE `farm_type` (
  `id` int(10) UNSIGNED NOT NULL,
  `farm` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `farm_type`
--

INSERT INTO `farm_type` (`id`, `farm`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Rice', 'approve', '2022-09-06 11:27:53', '2022-09-06 11:27:53'),
(2, 'Wheat', 'approve', '2022-09-06 11:27:53', '2022-09-06 11:27:53'),
(3, 'Maize', 'approve', '2022-09-06 11:27:53', '2022-09-06 11:27:53'),
(4, 'Others', NULL, '2022-09-06 11:27:53', '2022-09-06 11:27:53');

-- --------------------------------------------------------

--
-- Table structure for table `location`
--

CREATE TABLE `location` (
  `id` int(10) UNSIGNED NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `location`
--

INSERT INTO `location` (`id`, `location`, `created_at`, `updated_at`) VALUES
(1, 'Kaduna', '2022-09-06 11:40:58', '2022-09-06 11:40:58'),
(2, 'Lagos', '2022-09-06 11:40:58', '2022-09-06 11:40:58'),
(3, 'Niger', '2022-09-06 11:40:58', '2022-09-06 11:40:58'),
(4, 'Taraba', '2022-09-06 11:40:58', '2022-09-06 11:40:58');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(4, '2022_08_31_104418_create_role_table', 3),
(19, '2022_08_31_123524_create_orders_table', 12),
(20, '2022_08_31_133444_create_product_service_table', 13),
(21, '2022_09_04_200239_create_request_table', 14),
(23, '2022_08_31_110836_create_service_type_table', 16),
(24, '2022_08_31_092307_create_farm_type_table', 17),
(25, '2022_08_31_123357_create_location_table', 18),
(28, '2022_08_31_112848_create_profile_table', 21),
(29, '2022_09_06_144303_create_otp_table', 22),
(30, '2022_08_31_091949_create_users_table', 23),
(31, '2022_09_06_212712_create_country_table', 24);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `agent_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sp_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `land_hectare` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `farm_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `otp`
--

CREATE TABLE `otp` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `otp`
--

INSERT INTO `otp` (`id`, `code`, `created_at`, `updated_at`) VALUES
(1, 'iG2bCs', '2022-09-07 02:28:53', '2022-09-07 02:28:53'),
(2, 'iifmO3', '2022-09-07 10:07:09', '2022-09-07 10:07:09'),
(3, 'NAIFxf', '2022-09-07 10:10:23', '2022-09-07 10:10:23'),
(4, 'sK1mlO', '2022-09-07 10:11:30', '2022-09-07 10:11:30'),
(5, 'MN6I0n', '2022-09-07 10:31:10', '2022-09-07 10:31:10'),
(6, 'WIrALV', '2022-09-07 11:40:28', '2022-09-07 11:40:28'),
(7, 'honhjG', '2022-09-07 12:41:54', '2022-09-07 12:41:54');

-- --------------------------------------------------------

--
-- Table structure for table `product_service`
--

CREATE TABLE `product_service` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qty` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rent_sell` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profile`
--

CREATE TABLE `profile` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profile`
--

INSERT INTO `profile` (`id`, `user_id`, `email`, `business_name`, `address`, `location`, `bank_name`, `account_name`, `account_number`, `created_at`, `updated_at`) VALUES
(1, '1', 'methyl2007@yahoo.com', 'uyyy', '20 Adediran close', 'nigeria', 'gtbank', 'kasali Bran', '86734576', '2022-09-07 02:28:53', '2022-09-07 12:23:53'),
(2, '2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2022-09-07 10:11:30', '2022-09-07 10:11:30'),
(3, '3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2022-09-07 10:31:10', '2022-09-07 10:31:10'),
(4, '4', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2022-09-07 11:40:29', '2022-09-07 11:40:29'),
(5, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2022-09-07 12:41:55', '2022-09-07 12:41:55');

-- --------------------------------------------------------

--
-- Table structure for table `request`
--

CREATE TABLE `request` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `agent_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sp_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `land_hectare` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `farm_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `id` int(10) UNSIGNED NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`id`, `role`, `user_type`, `created_at`, `updated_at`) VALUES
(1, '1', 'superadmin', '2022-09-06 11:35:23', '2022-09-06 11:35:23'),
(2, '2', 'admin', '2022-09-06 11:35:23', '2022-09-06 11:35:23'),
(3, '3', 'agent', '2022-09-06 11:35:23', '2022-09-06 11:35:23'),
(4, '4', 'farmer', '2022-09-06 11:35:23', '2022-09-06 11:35:23'),
(5, '5', 'service', '2022-09-06 11:35:23', '2022-09-06 11:35:23');

-- --------------------------------------------------------

--
-- Table structure for table `service_type`
--

CREATE TABLE `service_type` (
  `id` int(10) UNSIGNED NOT NULL,
  `service` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_type`
--

INSERT INTO `service_type` (`id`, `service`, `created_at`, `updated_at`) VALUES
(1, 'Tractor', '2022-09-06 11:29:06', '2022-09-06 11:29:06'),
(2, 'Plower', '2022-09-06 11:29:06', '2022-09-06 11:29:06'),
(3, 'Planter', '2022-09-06 11:29:06', '2022-09-06 11:29:06'),
(4, 'Seed', '2022-09-06 11:29:06', '2022-09-06 11:29:06'),
(5, 'Pesticide', '2022-09-06 11:29:06', '2022-09-06 11:29:06'),
(6, 'Fertilizer', '2022-09-06 11:29:06', '2022-09-06 11:29:06'),
(7, 'Processor', '2022-09-06 11:29:06', '2022-09-06 11:29:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `ip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `farm_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reset_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reg_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `ip`, `country`, `user_type`, `name`, `farm_type`, `service_type`, `country_code`, `phone`, `password`, `reset_code`, `reg_code`, `status`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, '23:271:2:1:318', 'Nigeria', 'farmer', 'Mutiu', NULL, NULL, '23401', '08188373898', '$2y$10$Gf8RD7hexV9VJnF7qb9rv.761ybBYcZ4J5dZ3Pd/teDluNVY4/KTm', NULL, 'iG2bCs', 'pending', NULL, '2022-09-07 02:28:53', '2022-09-07 02:28:53'),
(2, '23:271:2:1:318', 'Nigeria', 'farmer', 'Mutiu', NULL, NULL, '+234', '08188373895', '$2y$10$yb5urKI7PaKJ.4DwTY.I/OZ3qUJPQ64YzCw9mnHYZ2V4tf.bdin.a', NULL, 'sK1mlO', 'pending', NULL, '2022-09-07 10:11:30', '2022-09-07 10:11:30'),
(3, '23:271:2:1:318', 'Nigeria', '4', 'Mutiu', 'rice', NULL, '+234', '08188373989', '$2y$10$XnYiJNPXCeb0raCUomHIf..uB.5ry8taiUlkeTXBxiUIR9PGZ6gOC', NULL, 'MN6I0n', 'pending', NULL, '2022-09-07 10:31:10', '2022-09-07 10:31:10'),
(4, '23:271:2:1:318', 'Nigeria', '4', 'Mutiu', 'rice', NULL, '+234', '08188373984', '$2y$10$yCJEezhj9SUbI6UpoE667uxTZG5z4A.5saff/67mWw2hh8ebyBX0K', NULL, 'WIrALV', 'pending', NULL, '2022-09-07 11:40:28', '2022-09-07 11:40:28'),
(5, '23:271:2:1:318', 'Nigeria', '4', 'Mutiu', 'rice', NULL, '+234', '08188373891', '$2y$10$eG.AK8UHflSRJ2JRtfireudCCV9wKCy4PLuvWSdGq/cbL0iFr5P02', NULL, 'honhjG', 'verified', NULL, '2022-09-07 12:41:55', '2022-09-07 12:41:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `country`
--
ALTER TABLE `country`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `farm_type`
--
ALTER TABLE `farm_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `otp`
--
ALTER TABLE `otp`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_service`
--
ALTER TABLE `product_service`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `profile`
--
ALTER TABLE `profile`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `profile_user_id_unique` (`user_id`),
  ADD UNIQUE KEY `profile_email_unique` (`email`);

--
-- Indexes for table `request`
--
ALTER TABLE `request`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `service_type`
--
ALTER TABLE `service_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_phone_unique` (`phone`),
  ADD UNIQUE KEY `users_reg_code_unique` (`reg_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `country`
--
ALTER TABLE `country`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `farm_type`
--
ALTER TABLE `farm_type`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `location`
--
ALTER TABLE `location`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `otp`
--
ALTER TABLE `otp`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `product_service`
--
ALTER TABLE `product_service`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `profile`
--
ALTER TABLE `profile`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `request`
--
ALTER TABLE `request`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `service_type`
--
ALTER TABLE `service_type`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
