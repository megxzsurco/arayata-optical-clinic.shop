-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 17, 2026 at 08:24 AM
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
-- Database: `arayata_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `patient_name` varchar(100) DEFAULT NULL,
  `service_type` varchar(100) DEFAULT NULL,
  `appointment_date` date DEFAULT NULL,
  `appointment_time` time DEFAULT NULL,
  `status` enum('Pending','Completed','Processing') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `patient_name`, `service_type`, `appointment_date`, `appointment_time`, `status`) VALUES
(1, 'john', 'Pickup: han', '2026-09-23', '23:34:00', 'Completed'),
(2, 'john', 'Eyeglasses Pickup', '2026-10-09', '00:24:00', 'Completed'),
(3, 'john', 'Comprehensive Eye Exam', '2026-09-10', '02:49:00', 'Processing'),
(4, 'ben', 'Comprehensive Eye Exam', '2026-09-22', '09:59:00', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `eye_examinations`
--

CREATE TABLE `eye_examinations` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `doctor_name` varchar(100) DEFAULT NULL,
  `sph_od` varchar(20) DEFAULT NULL,
  `cyl_od` varchar(20) DEFAULT NULL,
  `axis_od` varchar(20) DEFAULT NULL,
  `sph_os` varchar(20) DEFAULT NULL,
  `cyl_os` varchar(20) DEFAULT NULL,
  `axis_os` varchar(20) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `exam_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `face_analyses`
--

CREATE TABLE `face_analyses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `patient_name` varchar(100) NOT NULL,
  `face_shape` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `face_analyses`
--

INSERT INTO `face_analyses` (`id`, `user_id`, `patient_name`, `face_shape`, `created_at`) VALUES
(1, 4, 'bern', 'Square', '2026-09-24 13:06:16'),
(2, 4, 'bern', 'Square', '2026-09-24 13:23:06'),
(3, 4, 'bern', 'Square', '2026-09-24 13:23:32'),
(4, 4, 'bern', 'Square', '2026-09-24 13:23:43'),
(5, 4, 'bern', 'Square', '2026-09-24 13:24:14'),
(6, 4, 'bern', 'Square', '2026-09-24 13:24:14'),
(7, 4, 'bern', 'Square', '2026-09-24 13:31:58'),
(8, 4, 'bern', 'Square', '2026-09-24 13:57:00'),
(9, 4, 'bern', 'Square', '2026-09-24 13:57:00'),
(10, 4, 'bern', 'Square', '2026-09-24 14:53:46'),
(11, 8, 'ta', 'Round', '2026-09-28 10:56:17'),
(12, 8, 'ta', 'Square', '2026-09-28 10:56:17'),
(13, 8, 'ta', 'Square', '2026-09-28 10:56:17'),
(14, 8, 'ta', 'Square', '2026-09-28 10:56:17'),
(15, 6, 'tans ', 'Square', '2026-09-29 13:02:18'),
(16, 8, 'ta', 'Square', '2026-10-03 14:23:17'),
(17, 8, 'ta', 'Square', '2026-10-03 14:23:24'),
(18, 8, 'ta', 'Round', '2026-10-03 14:49:09'),
(19, 8, 'ta', 'Square', '2026-10-03 15:17:28'),
(20, 8, 'ta', 'Square', '2026-10-03 15:22:38'),
(21, 8, 'ta', 'Square', '2026-10-03 15:22:49'),
(22, 8, 'ta', 'Square', '2026-10-03 15:23:10'),
(23, 8, 'ta', 'Round', '2026-10-03 15:23:23'),
(24, 10, 'bry', 'Square', '2026-10-08 14:17:42'),
(25, 13, 'efyuyiuoip', 'Square', '2026-10-09 04:22:23'),
(26, 13, 'efyuyiuoip', 'Square', '2026-10-09 04:22:37'),
(27, 13, 'efyuyiuoip', 'Round', '2026-10-09 04:22:49'),
(28, 13, 'efyuyiuoip', 'Round', '2026-10-09 04:23:10'),
(29, 13, 'efyuyiuoip', 'Square', '2026-10-09 04:23:25'),
(30, 13, 'efyuyiuoip', 'Square', '2026-10-09 04:24:48'),
(31, 15, 'james', 'Oval', '2026-10-12 14:05:09'),
(32, 15, 'james', 'Oval', '2026-10-12 14:05:30'),
(33, 15, 'james', 'Oval', '2026-10-12 14:05:52'),
(34, 15, 'james', 'Oval', '2026-10-12 14:06:08'),
(35, 15, 'james', 'Round', '2026-10-12 14:06:48'),
(36, 18, 'Customer Name', 'Oval', '2026-10-14 09:49:13'),
(37, 18, 'Customer Name', 'Oval', '2026-10-14 09:49:13'),
(38, 18, 'Customer Name', 'Oval', '2026-10-14 09:49:13'),
(39, 15, 'james', 'Oval', '2026-10-14 10:56:40'),
(40, 19, 'juan', 'Oval', '2026-10-17 05:27:24'),
(41, 19, 'juan', 'Round', '2026-10-17 05:27:24'),
(42, 19, 'juan', 'Round', '2026-10-17 05:27:24'),
(43, 19, 'juan', 'Round', '2026-10-17 05:27:24'),
(44, 19, 'juan', 'Round', '2026-10-17 05:27:24'),
(45, 19, 'juan', 'Round', '2026-10-17 05:27:24'),
(46, 19, 'juan', 'Round', '2026-10-17 05:27:24'),
(47, 19, 'juan', 'Heart', '2026-10-17 05:27:48'),
(48, 19, 'juan', 'Oval', '2026-10-17 05:27:48'),
(49, 1, 'System Admin', 'Round', '2026-10-17 06:06:17'),
(50, 1, 'System Admin', 'Oval', '2026-10-17 06:06:27'),
(51, 1, 'System Admin', 'Oval', '2026-10-17 06:06:38');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `category` enum('Frame','Lens','Accessory') NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `price` decimal(10,2) NOT NULL,
  `branch` varchar(50) DEFAULT 'Tanza Branch',
  `image` varchar(255) DEFAULT NULL,
  `colors` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `product_name`, `category`, `stock`, `price`, `branch`, `image`, `colors`) VALUES
(1, 'Classic Aviator Frame', 'Frame', 36, 1500.00, 'Tanza Branch', 'classic av.jpg', 'Black: 4,Silver: 12,Gold: 11,Purple: 9'),
(2, 'Blue Light Shield Lens', 'Lens', 12, 850.00, 'Tanza Branch', 'Blue Light.jpg', 'Black: 8,Silver: 4'),
(3, 'Anti-Glare Round Frame', 'Frame', 29, 1200.00, 'Tanza Branch', 'anti-glare.jpg', 'Black, Silver, Gold'),
(5, 'Isometal', 'Frame', 20, 1200.00, 'Tanza Branch', '1791819474_Isometal.jpg', 'Black, Silver, Gold'),
(6, 'Isomplastic', 'Frame', 25, 950.00, 'Tanza Branch', '1791819526_Isomplastic.jpg', 'Black, Silver, Gold'),
(7, 'Sporty type', 'Frame', 15, 1400.00, 'Tanza Branch', '1791820165_Sporty_type.jfif', 'Black, Silver, Gold'),
(8, 'Metal frame / sporty type', 'Frame', 18, 1600.00, 'Tanza Branch', '1791823806_Metal_frame___sporty_type.jpg', 'Black, Silver, Gold'),
(9, 'Metal frame', 'Frame', 30, 1100.00, 'Tanza Branch', '', 'Black, Silver, Gold'),
(10, 'Full rim / half rim', 'Frame', 22, 1300.00, 'Tanza Branch', '', 'Black, Silver, Gold'),
(11, 'Kids / Flexible', '', 40, 850.00, 'Tanza Branch', '', 'Black, Silver, Gold'),
(12, 'Titanium frame / flexible', 'Frame', 10, 2500.00, 'Tanza Branch', '', 'Black, Silver, Gold'),
(13, 'Aviator Frame', 'Frame', 1, 1500.00, 'Tanza Branch', '1792217282_AviatorFrame.png', 'Black:1, Silver, Gold'),
(14, 'Cat eye Frame', 'Frame', 20, 1350.00, 'Tanza Branch', '', 'Black, Silver, Gold'),
(15, 'Round Frame', 'Frame', 30, 1200.00, 'Tanza Branch', 'lens.jpg', 'Black, Silver, Gold'),
(18, 'juan', 'Frame', 17, 900.00, 'Tanza Branch', '1792214582_juan.jpg', 'blue: 8,black: 9'),
(19, 'juan', 'Frame', 18, 900.00, 'Tanza Branch', '1792214620_juan.jpg', 'blue:8,black:10'),
(20, 'juan', 'Frame', 18, 900.00, 'Tanza Branch', '1792214631_juan.jpg', 'blue:8,black:10');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `color` varchar(100) DEFAULT 'Standard'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `item_id`, `user_id`, `product_name`, `category`, `price`, `status`, `created_at`, `color`) VALUES
(1, NULL, 8, 'Isometal', 'Metal Frame', 1500.00, 'Completed', '2026-10-03 15:57:28', 'Standard'),
(2, NULL, 10, 'Isometal', 'Metal Frame', 1500.00, 'Completed', '2026-10-08 14:18:11', 'Standard'),
(3, NULL, 13, 'Isometal', 'Metal Frame', 1500.00, 'Pending', '2026-10-09 04:25:22', 'Standard'),
(4, NULL, 15, 'Classic Aviator Frame', 'Frame', 1500.00, 'Cancelled', '2026-10-13 06:55:00', 'Black'),
(5, NULL, 15, 'Classic Aviator Frame', 'Frame', 1500.00, 'Reserved', '2026-10-13 06:55:04', 'Black'),
(6, NULL, 15, 'Classic Aviator Frame', 'Frame', 1500.00, 'Reserved', '2026-10-13 06:55:13', 'Purple'),
(7, NULL, 15, 'Classic Aviator Frame', 'Frame', 1500.00, 'Reserved', '2026-10-13 07:08:47', 'Black'),
(8, NULL, 15, 'Classic Aviator Frame', 'Frame', 1500.00, 'Completed', '2026-10-13 07:18:31', 'Black'),
(9, NULL, 15, 'Classic Aviator Frame', 'Frame', 1500.00, 'Completed', '2026-10-13 07:18:36', 'Black'),
(10, NULL, 15, 'Classic Aviator Frame', 'Frame', 1500.00, 'Cancelled', '2026-10-13 07:18:47', 'Silver'),
(11, NULL, 15, 'Classic Aviator Frame', 'Frame', 1500.00, 'Cancelled', '2026-10-13 07:27:57', 'Silver'),
(12, NULL, 15, 'Classic Aviator Frame', 'Frame', 1500.00, 'Cancelled', '2026-10-13 07:46:47', 'Silver'),
(13, NULL, 15, 'Classic Aviator Frame', 'Frame', 1500.00, 'Completed', '2026-10-13 07:59:38', 'Silver'),
(14, NULL, 15, 'Classic Aviator Frame', 'Frame', 1500.00, 'Completed', '2026-10-13 07:59:46', 'Black'),
(15, NULL, 15, 'Classic Aviator Frame', 'Frame', 1500.00, 'Completed', '2026-10-13 08:04:03', 'Black'),
(16, NULL, 15, 'Classic Aviator Frame', 'Frame', 1500.00, 'Completed', '2026-10-13 08:04:17', 'Black'),
(17, NULL, 15, 'Classic Aviator Frame', 'Frame', 1500.00, 'Completed', '2026-10-13 08:04:49', 'Black'),
(18, NULL, 15, 'Classic Aviator Frame', 'Frame', 1500.00, 'Completed', '2026-10-13 08:06:31', 'Black'),
(19, NULL, 15, 'Classic Aviator Frame', 'Frame', 1500.00, 'Completed', '2026-10-13 08:12:29', 'Silver'),
(20, NULL, 18, 'Blue Light Shield Lens', 'Lens', 850.00, 'Cancelled', '2026-10-15 03:40:56', 'Silver'),
(21, NULL, 18, 'Blue Light Shield Lens', 'Lens', 850.00, 'Cancelled', '2026-10-15 03:55:32', 'Silver'),
(22, NULL, 18, 'Blue Light Shield Lens', 'Lens', 850.00, 'Cancelled', '2026-10-15 03:56:05', 'Silver'),
(23, NULL, 18, 'Blue Light Shield Lens', 'Lens', 850.00, 'Cancelled', '2026-10-15 03:59:00', 'Silver'),
(24, NULL, 18, 'Blue Light Shield Lens', 'Lens', 850.00, 'Cancelled', '2026-10-15 04:00:05', 'Silver'),
(25, 2, 18, 'Blue Light Shield Lens', 'Lens', 850.00, 'Cancelled', '2026-10-15 04:11:09', 'Silver'),
(26, 1, 18, 'Classic Aviator Frame', 'Frame', 1500.00, 'Completed', '2026-10-15 04:15:40', 'Black'),
(27, 1, 18, 'Classic Aviator Frame', 'Frame', 1500.00, 'Completed', '2026-10-15 04:16:26', 'Black'),
(28, 1, 18, 'Classic Aviator Frame', 'Frame', 1500.00, 'Cancelled', '2026-10-15 04:17:02', 'Gold'),
(29, 2, 18, 'Blue Light Shield Lens', 'Lens', 850.00, 'Cancelled', '2026-10-15 04:27:51', 'Black'),
(30, 2, 18, 'Blue Light Shield Lens', 'Lens', 850.00, 'Completed', '2026-10-15 04:28:51', 'Silver'),
(31, 1, 18, 'Classic Aviator Frame', 'Frame', 1500.00, 'Cancelled', '2026-10-15 04:32:09', 'Purple'),
(32, 1, 18, 'Classic Aviator Frame', 'Frame', 1500.00, 'Completed', '2026-10-15 04:32:13', 'Gold'),
(33, 1, 18, 'Classic Aviator Frame', 'Frame', 1500.00, 'Completed', '2026-10-15 04:48:45', 'Black'),
(34, 18, 19, 'juan', 'Frame', 900.00, 'Completed', '2026-10-17 05:23:32', 'black'),
(35, 1, 19, 'Classic Aviator Frame', 'Frame', 1500.00, 'Cancelled', '2026-10-17 05:24:39', 'Silver'),
(36, 2, 19, 'Blue Light Shield Lens', 'Lens', 850.00, 'Completed', '2026-10-17 05:25:22', 'Black'),
(37, 13, 1, 'Aviator Frame', 'Frame', 1500.00, 'Reserved', '2026-10-17 06:09:42', 'Black'),
(38, 1, 1, 'Classic Aviator Frame', 'Frame', 1500.00, 'Cancelled', '2026-10-17 06:11:54', 'Gold'),
(39, 2, 1, 'Blue Light Shield Lens', 'Lens', 850.00, 'Completed', '2026-10-17 06:12:48', 'Black'),
(40, 1, 1, 'Classic Aviator Frame', 'Frame', 1500.00, 'Completed', '2026-10-17 06:18:58', 'Purple');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `contact_number` int(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `queue_no` varchar(50) NOT NULL,
  `status` varchar(50) DEFAULT 'Processing'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `first_name`, `last_name`, `age`, `gender`, `contact_number`, `email`, `created_at`, `queue_no`, `status`) VALUES
(1, 'john', 'tan', 23, 'Male', 2147483647, NULL, '2026-09-23 15:32:31', '', 'Processing'),
(2, 'ass', 'ss', 5, 'Male', 0, NULL, '2026-10-03 16:51:13', '', 'Processing'),
(3, 'James', 'Laconsay', 14, 'Female', 99, NULL, '2026-10-14 08:26:54', 'Q-103', 'Processing'),
(4, 'James', 'Laconsay', 14, 'Female', 99, NULL, '2026-10-14 08:27:40', 'Q-104', 'Processing'),
(5, 'Jamesww', 'Laconsay', 1234, 'Male', 12222, NULL, '2026-10-14 08:27:56', 'Q-105', 'Processing');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(30) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `customer_name`, `total_amount`, `payment_method`, `created_at`) VALUES
(1, 'bern', 1000.00, 'Cash', '2026-09-23 16:53:06'),
(2, 'Online Customer (User #16)', 1500.00, 'Online', '2026-10-15 02:37:09'),
(3, 'Online Customer (User #15)', 1500.00, 'Online', '2026-10-15 03:15:10'),
(4, 'James', 1500.00, 'Cash', '2026-10-15 03:17:31'),
(5, 'Online Customer (User #15)', 1500.00, 'Online', '2026-10-15 03:36:45'),
(6, 'Online Customer (User #15)', 1500.00, 'Online', '2026-10-15 04:14:49'),
(7, 'Online Customer (User #15)', 1500.00, 'Online', '2026-10-15 04:15:16'),
(8, 'Online Customer (User #18)', 1500.00, 'Online', '2026-10-15 04:15:59'),
(9, 'Online Customer (User #18)', 1500.00, 'Online', '2026-10-15 04:16:47'),
(10, 'Online Customer (User #18)', 1500.00, 'Online', '2026-10-15 04:24:17'),
(11, 'Online Customer (User #18)', 850.00, 'Online', '2026-10-15 04:29:05'),
(12, 'Online Customer (User #18)', 1500.00, 'Online', '2026-10-15 04:32:50'),
(13, 'AkoSiCustomer', 1500.00, 'Online', '2026-10-15 04:48:56'),
(14, 'juan', 900.00, 'Online', '2026-10-17 05:24:07'),
(15, 'juan', 850.00, 'Online', '2026-10-17 05:26:03'),
(16, 'System Admin', 850.00, 'Online', '2026-10-17 06:13:14'),
(17, 'ah', 1200.00, 'GCash', '2026-10-17 06:15:13'),
(18, 'System Admin', 1500.00, 'Online', '2026-10-17 06:19:20');

-- --------------------------------------------------------

--
-- Table structure for table `tryon_frames`
--

CREATE TABLE `tryon_frames` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tryon_frames`
--

INSERT INTO `tryon_frames` (`id`, `name`, `description`, `image_url`, `created_at`) VALUES
(8, 'Classic Aviator', 'Metal Wire Teardrop', '1791980653_classic_av-removebg-preview.png', '2026-10-14 12:24:13'),
(9, 'Square Frame', 'Moder glasses', 'squareframe.jpg', '2026-10-17 05:54:27'),
(10, 'Vision Frame', 'Vision glasses', 'VisionGlass.jpg', '2026-10-17 06:01:32');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Doctor','Staff','Customer') DEFAULT 'Customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'System Admin', 'admin@arayata.com', '$2y$10$CfAIfQ1AuZvOuJLqb3fPm.2CRBaVzKC1o/qWFLZfFwu/jclAu6Rwe', 'Admin', '2026-09-23 14:29:19'),
(2, 'john', 'john@gmail.com', '$2y$10$AnF7ORjTFuy4Je4GIvZlVe7nu/k2MguTFzD12ge7xicStR0yX9ARa', 'Customer', '2026-09-23 15:47:53'),
(3, 'john', 'tan@gmail.com', '$2y$10$uf7j5aULNSoGXDZYXzMMdeq8nMEy0qRD/S10m4i.DSRcFLFYLJogC', 'Customer', '2026-09-23 16:20:37'),
(4, 'bern', 'bern@gmail.com', '$2y$10$LiG8EJ6gXheHQMCiDRGYueXaJQ.fTwtBqEYbBkhfDqSXj29HtDW7q', 'Admin', '2026-09-23 16:22:17'),
(5, 'ben', 'ben@gmail.com', '$2y$10$ugXD598StZ21JINXsQMMReKVurZU/3LLa6tkcilP4nykD3Y1/n7Zy', 'Customer', '2026-09-24 12:42:15'),
(6, 'tans ', 'tans@gmail.com', '$2y$10$MDMQiBeKStnZ5QzPcv4o..hIZnIs6f7P1BJsiW0tqAsGHr7sbVBpO', 'Admin', '2026-09-28 08:25:07'),
(7, 'joh', 'joh@gmail.om', '$2y$10$jVrmM6xi0OkblVsEqINi/eVsKXRKmGjMGiLldpT5vp8nqN/vdYKgG', 'Customer', '2026-09-28 08:33:25'),
(8, 'ta', 'ta@gmail.com', '$2y$10$cx9Nr1uWaS6kC6CUT5IhlO3ndaL1UZpVGyL4fW6B9akZNoKodk2LO', 'Customer', '2026-09-28 10:35:58'),
(9, 'james', '123@gmail.com', '$2y$10$pBtR2Y5Fidmc1MWPVw6vs.WDUwXP0JHPr.KOwlkSXEvzg8uXp.4UK', 'Customer', '2026-10-08 14:16:11'),
(10, 'bry', '12q3@gmail.com', '$2y$10$DNKb6dvOUShGdsjTx7/gmuLiAVMb4vX1SlNn9lEpbUiaa0/651idG', 'Customer', '2026-10-08 14:16:44'),
(11, 're', '12312@gmail.com', '$2y$10$m3MmKwAZfmIpU2jN226I6.08afnXlyybICtGfiYmMiKubdYGfcUMO', 'Customer', '2026-10-08 14:19:04'),
(12, 'rtyui', 'jan@gmail.com', '$2y$10$7shjQ8Yysr8E4OUTFQFsxO6ON6awYW4mjZN6xI6CYXVZmJYqKSPhO', 'Admin', '2026-10-08 14:19:49'),
(13, 'efyuyiuoip', 'qwre@gmail.com', '$2y$10$bYnivDmUU5OE5Bwa0zVfM.APEPZtIOd2wFgC.VwTHJDeorzKboH6S', 'Customer', '2026-10-09 04:21:20'),
(14, 'mnbbv', 'mnbbv@gmail.com', '$2y$10$p/sD3oH47c6LOiLsFez8DORS.2V/vWiyQOEQw2g/mUAfE7yh1bdLO', 'Admin', '2026-10-09 04:28:34'),
(15, 'james', 'james@gmail.com', '$2y$10$VPbZIT8P61kszez/IWVU6uQCchLxNdDuyKTFFrbtDT04i65aYGgGe', 'Customer', '2026-10-12 14:00:14'),
(18, 'AkoSiCustomer', '1234@gmail.com', '$2y$10$Bxs2Uh5XYVXR0f7tvXdlB.ik8dfHjhqtWPnOatTbWhpWMfEHw3fwG', 'Customer', '2026-10-14 09:22:38'),
(19, 'juan', 'juan@gmail.com', '$2y$10$iH29T59PjFhccVLA2vA5KuzTdMeArWdJgPtJXdOCFRlPEbX/i8Ehm', 'Customer', '2026-10-17 05:19:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eye_examinations`
--
ALTER TABLE `eye_examinations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `face_analyses`
--
ALTER TABLE `face_analyses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tryon_frames`
--
ALTER TABLE `tryon_frames`
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
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `eye_examinations`
--
ALTER TABLE `eye_examinations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `face_analyses`
--
ALTER TABLE `face_analyses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tryon_frames`
--
ALTER TABLE `tryon_frames`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `eye_examinations`
--
ALTER TABLE `eye_examinations`
  ADD CONSTRAINT `eye_examinations_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
