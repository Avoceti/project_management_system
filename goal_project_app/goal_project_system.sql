-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 29, 2025 at 06:54 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `goal_project_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `village` varchar(100) DEFAULT NULL,
  `ta` varchar(100) DEFAULT NULL,
  `gvh` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `tribe` varchar(100) DEFAULT NULL,
  `current_town` varchar(100) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `nid` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `marital_status` enum('Single','Married','Divorced','Widowed') DEFAULT 'Single',
  `sex` enum('Male','Female','Other') NOT NULL,
  `salary` decimal(12,2) DEFAULT 0.00,
  `category_id` int(11) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `employee_id`, `full_name`, `village`, `ta`, `gvh`, `district`, `tribe`, `current_town`, `dob`, `nid`, `phone`, `nationality`, `photo`, `marital_status`, `sex`, `salary`, `category_id`, `status`, `created_at`, `updated_at`) VALUES
(7, 'EMP0001', 'Lenard Kamagalasi', 'Ntcheu', 'Jolomore', 'Mafuta', 'Blantyre', 'Nsena', 'Blantyre', '1993-09-07', 'WWRE2537', '0998746446', 'Malawian', 'uploads/68d6b1596d11a.jpg', 'Married', 'Male', 50000.00, 4, 'Active', '2025-09-26 15:29:29', '2025-09-27 10:05:23'),
(8, 'EMP0008', 'Tadala kachipande', 'Ntcheu', 'Jolomore', 'Mafuta', 'Blantyre', 'Nsena', 'Blantyre', '2000-09-09', 'WWRE2532', '0998746446', 'Malawian', 'uploads/68d76e63619e6.jpg', 'Married', 'Female', 50000.00, 3, 'Active', '2025-09-27 04:56:03', '2025-09-28 08:49:03');

-- --------------------------------------------------------

--
-- Table structure for table `employee_attendance`
--

CREATE TABLE `employee_attendance` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('Present','Absent','Leave','Holiday') NOT NULL DEFAULT 'Present',
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_attendance`
--

INSERT INTO `employee_attendance` (`id`, `employee_id`, `date`, `status`, `check_in`, `check_out`, `notes`, `created_at`, `updated_at`) VALUES
(6, 7, '2025-09-28', 'Present', '07:30:00', '16:30:00', 'Present', '2025-09-28 08:50:18', '2025-09-28 08:50:18');

-- --------------------------------------------------------

--
-- Table structure for table `employee_categories`
--

CREATE TABLE `employee_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_categories`
--

INSERT INTO `employee_categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(2, 'Manager', NULL, '2025-09-24 14:57:16', NULL),
(3, 'Accountant', NULL, '2025-09-24 14:57:16', NULL),
(4, 'Driver', NULL, '2025-09-24 14:57:16', NULL),
(5, 'Cleaner', NULL, '2025-09-24 14:57:16', NULL),
(6, 'Security Guard', NULL, '2025-09-24 14:57:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_financials`
--

CREATE TABLE `employee_financials` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_financials`
--

INSERT INTO `employee_financials` (`id`, `employee_id`, `bank_name`, `account_number`, `account_name`, `photo`, `created_at`, `updated_at`) VALUES
(1, 7, 'TNM Mpamba', '0995082205', 'Prince Juma', NULL, '2025-09-26 22:34:14', '2025-09-26 22:34:14');

-- --------------------------------------------------------

--
-- Table structure for table `employee_payments`
--

CREATE TABLE `employee_payments` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_date` date NOT NULL,
  `method_id` int(11) NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `allowance` decimal(12,2) DEFAULT 0.00,
  `total_payment` decimal(12,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_payments`
--

INSERT INTO `employee_payments` (`id`, `employee_id`, `amount`, `payment_date`, `method_id`, `notes`, `created_at`, `allowance`, `total_payment`) VALUES
(21, 7, 50000.00, '2025-09-26', 1, 'Paid', '2025-09-26 15:32:43', 10000.00, 60000.00);

-- --------------------------------------------------------

--
-- Table structure for table `goals`
--

CREATE TABLE `goals` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date NOT NULL DEFAULT curdate(),
  `deadline` date DEFAULT NULL,
  `target_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `current_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `progress` decimal(5,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','In Progress','Completed') DEFAULT 'Pending',
  `viewed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `goals`
--

INSERT INTO `goals` (`id`, `title`, `description`, `start_date`, `deadline`, `target_amount`, `current_amount`, `progress`, `created_at`, `status`, `viewed`) VALUES
(16, 'Funds For 10 Local Chickens', 'Raise Funds for 100 Lock chicks', '2025-09-23', '2026-01-23', 85000.00, 0.00, 0.00, '2025-09-23 10:37:30', 'In Progress', 0),
(17, 'Raise Money For Cow House', 'Money For Cow House', '2025-05-26', '2025-11-01', 623500.00, 428000.00, 0.00, '2025-09-26 15:55:46', 'In Progress', 0),
(18, 'Funds For a Chicken House', 'Build a Chicken House', '2025-09-26', '2025-12-30', 300000.00, 0.00, 0.00, '2025-09-26 16:06:32', 'In Progress', 0),
(19, 'Raise Money For Cow', 'Buy Cow', '2025-09-26', '2026-02-01', 800000.00, 0.00, 0.00, '2025-09-26 17:32:52', 'In Progress', 0),
(20, 'Buy 6 bags of Fertilizer ', 'Buy fertilizer', '2025-09-26', '2026-12-30', 900000.00, 450000.00, 50.00, '2025-09-26 18:32:05', 'In Progress', 0),
(21, 'Raise Money For Bather Building', 'Short term goal Raise Money For Bather Building', '2025-09-28', '2025-09-29', 52000.00, 52000.00, 38.33, '2025-09-28 05:15:09', 'Completed', 0);

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `name`, `details`, `created_at`) VALUES
(1, 'Cash', NULL, '2025-09-24 04:47:31'),
(2, 'Airtel money', NULL, '2025-09-24 04:48:06'),
(4, 'Bank', NULL, '2025-09-26 10:48:28'),
(5, 'TNM Mpamba', NULL, '2025-09-26 10:48:34'),
(6, 'TNM Mpamba', NULL, '2025-09-26 10:48:49');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date NOT NULL DEFAULT curdate(),
  `deadline` date DEFAULT NULL,
  `target_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `current_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `progress` decimal(5,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','In Progress','Completed') DEFAULT 'Pending',
  `viewed` tinyint(1) DEFAULT 0,
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `title`, `description`, `start_date`, `deadline`, `target_amount`, `current_amount`, `progress`, `created_at`, `status`, `viewed`, `category_id`) VALUES
(6, 'Build Cow House', 'Build A house For Daily Cow', '2025-06-23', '2026-01-23', 703500.00, 128000.00, 18.19, '2025-09-23 10:39:50', 'In Progress', 0, 2),
(7, 'Build Chicken House', 'Build a Lock chicken house', '2025-09-26', '2025-12-30', 300000.00, 0.00, 0.00, '2025-09-26 17:38:42', 'In Progress', 0, 3),
(8, 'Build a Bather', 'Building a Bather', '2025-09-28', '2025-09-28', 23000.00, 23000.00, 100.00, '2025-09-28 05:42:59', 'Completed', 0, 4);

-- --------------------------------------------------------

--
-- Table structure for table `project_categories`
--

CREATE TABLE `project_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_categories`
--

INSERT INTO `project_categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Construct A Cow House', NULL, '2025-09-23 08:02:19'),
(2, 'Cattle Farming', NULL, '2025-09-23 10:40:08'),
(3, 'Chicken Farming', NULL, '2025-09-26 17:39:08'),
(4, 'Construction', NULL, '2025-09-28 05:43:34');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Manager','User') NOT NULL DEFAULT 'User',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(3, 'Admin', 'admin@example.com', '$2y$10$thtVoNen9uK/3gMKEkZ5ruaQKx0iFDvFcdpQFJdEe01lkVJkIMp9q', 'Admin', '2025-09-23 10:14:42'),
(5, 'Jedidiah Jumah', 'admin3@example.com', '$2y$10$0JjmrOX.MkGJcrcyaF7npe5f5OUqgsulE4lAXDsC9TshwT0466oVS', 'Admin', '2025-09-23 10:19:10'),
(7, 'Jedidiah Jumah', 'princejumah3655@gmail.com', '$2y$10$0h3iJXbUK0zTFB2JF21fEOwiaZkIKIf.4cNhlPfl09l.6rmY/vAk.', 'Manager', '2025-09-27 20:01:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `employee_attendance`
--
ALTER TABLE `employee_attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_employee_attendance` (`employee_id`);

--
-- Indexes for table `employee_categories`
--
ALTER TABLE `employee_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employee_financials`
--
ALTER TABLE `employee_financials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id_unique` (`employee_id`);

--
-- Indexes for table `employee_payments`
--
ALTER TABLE `employee_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `method_id` (`method_id`),
  ADD KEY `employee_payments_ibfk_1` (`employee_id`);

--
-- Indexes for table `goals`
--
ALTER TABLE `goals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `project_categories`
--
ALTER TABLE `project_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `employee_attendance`
--
ALTER TABLE `employee_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `employee_categories`
--
ALTER TABLE `employee_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `employee_financials`
--
ALTER TABLE `employee_financials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `employee_payments`
--
ALTER TABLE `employee_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `goals`
--
ALTER TABLE `goals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `project_categories`
--
ALTER TABLE `project_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `employee_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `employee_attendance`
--
ALTER TABLE `employee_attendance`
  ADD CONSTRAINT `fk_employee_attendance` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_financials`
--
ALTER TABLE `employee_financials`
  ADD CONSTRAINT `fk_employee_financials_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee_payments`
--
ALTER TABLE `employee_payments`
  ADD CONSTRAINT `employee_payments_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_payments_ibfk_2` FOREIGN KEY (`method_id`) REFERENCES `payment_methods` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
