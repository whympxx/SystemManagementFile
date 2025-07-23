-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 23, 2025 at 06:18 AM
-- Server version: 10.4.32-MariaDB-log
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `filemanager_pro`
--

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `folder_id` int(11) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `files`
--

INSERT INTO `files` (`id`, `filename`, `original_name`, `file_path`, `file_size`, `file_type`, `mime_type`, `upload_date`, `is_deleted`, `created_at`, `updated_at`, `folder_id`, `password`) VALUES
(1, '1753202378_3dc26f18c9157fbe.pdf', 'TheFutureofAI.pdf', 'uploads/1753202378_3dc26f18c9157fbe.pdf', 242069, 'pdf', 'application/pdf', '2025-07-22 16:39:38', 1, '2025-07-22 16:39:38', '2025-07-22 20:38:26', NULL, NULL),
(2, '1753204447_9a4823f566338e84.png', 'Screenshot 2025-07-20 144629.png', 'uploads/1753204447_9a4823f566338e84.png', 19199, 'png', 'image/png', '2025-07-22 17:14:07', 1, '2025-07-22 17:14:07', '2025-07-22 20:38:20', NULL, NULL),
(3, '1753216741_56a4f0a97e372f65.png', 'Screenshot 2025-07-22 121318.png', 'uploads/1753216741_56a4f0a97e372f65.png', 1060, 'png', 'image/png', '2025-07-22 20:39:01', 1, '2025-07-22 20:39:01', '2025-07-22 20:39:49', NULL, NULL),
(4, '1753216824_cb561d57abaf2365.png', 'Screenshot 2025-07-20 144629.png', 'uploads/1753216824_cb561d57abaf2365.png', 19199, 'png', 'image/png', '2025-07-22 20:40:24', 1, '2025-07-22 20:40:24', '2025-07-23 01:53:01', NULL, NULL),
(5, '1753235340_448af205fc1536b1.png', 'Screenshot 2025-07-23 083654.png', 'uploads/1753235340_448af205fc1536b1.png', 12346, 'png', 'image/png', '2025-07-23 01:49:00', 1, '2025-07-23 01:49:00', '2025-07-23 03:07:20', NULL, NULL),
(6, '1753235576_4d031000.png', 'Copy of Screenshot 2025-07-20 144629.png', 'uploads/1753235576_4d031000.png', 19199, 'png', 'image/png', '2025-07-23 01:52:56', 0, '2025-07-23 01:52:56', '2025-07-23 03:15:53', 18, '$2y$10$.ZMOHJt45s4Xr1bIjxR8/OAKuwvCUs0eboEgwLFWJFAul3RQ7tgV6'),
(11, '1753240036_55e03e0e.png', 'Copy of Screenshot 2025-07-23 083654.png', 'uploads/1753240036_55e03e0e.png', 12346, 'png', 'image/png', '2025-07-23 03:07:16', 1, '2025-07-23 03:07:16', '2025-07-23 03:13:13', NULL, NULL),
(13, '1753242779_42f60b70ae59ad76.jpg', 'Kim_Jong-un_April_2019_(cropped).jpg', 'uploads/1753242779_42f60b70ae59ad76.jpg', 180890, 'jpg', 'image/jpeg', '2025-07-23 03:52:59', 0, '2025-07-23 03:52:59', '2025-07-23 03:52:59', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `file_shares`
--

CREATE TABLE `file_shares` (
  `id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `share_token` varchar(64) NOT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `download_count` int(11) DEFAULT 0,
  `max_downloads` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `folders`
--

CREATE TABLE `folders` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `folders`
--

INSERT INTO `folders` (`id`, `name`, `parent_id`, `created_at`, `updated_at`, `password`) VALUES
(17, 'berkas negara', NULL, '2025-07-23 09:49:53', '2025-07-23 10:54:15', NULL),
(18, 'rahasia', NULL, '2025-07-23 09:56:02', '2025-07-23 09:56:18', '$2y$10$xigz3rsD7ayrQhL.gxrz6Oj/8Iy0hi1EKCdRWOGnABnkEoFFXrpoG');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `profile_photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `is_active`, `created_at`, `updated_at`, `profile_photo`) VALUES
(1, 'Wahyu', 'whyump777sky@gmail.com', '$2y$10$RaachJzESKKbvLnCwgELtu5lRpPh9MpA8mLNX64IUS0rxQoG91pD.', 'Wahyu', 1, '2025-07-22 13:07:53', '2025-07-23 03:50:34', 'profile_photos/profile_1_1753242634.jpg'),
(2, 'testuser', 'test@example.com', '$2y$10$q2pcy7NXlWAZfmH1zzKBaO74VjdNBreEL38D4/zBwH.v4cykgLHRK', 'Test User', 1, '2025-07-22 15:39:38', '2025-07-22 15:39:38', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dark_mode` tinyint(1) DEFAULT 0,
  `language` varchar(10) DEFAULT 'id',
  `notifications` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_settings`
--

INSERT INTO `user_settings` (`id`, `user_id`, `dark_mode`, `language`, `notifications`, `created_at`, `updated_at`) VALUES
(1, 1, 0, 'id', 1, '2025-07-23 03:37:37', '2025-07-23 03:37:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_filename` (`filename`),
  ADD KEY `idx_file_type` (`file_type`),
  ADD KEY `idx_upload_date` (`upload_date`),
  ADD KEY `idx_is_deleted` (`is_deleted`),
  ADD KEY `folder_id` (`folder_id`);

--
-- Indexes for table `file_shares`
--
ALTER TABLE `file_shares`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `share_token` (`share_token`),
  ADD KEY `idx_share_token` (`share_token`),
  ADD KEY `idx_file_id` (`file_id`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `folders`
--
ALTER TABLE `folders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `file_shares`
--
ALTER TABLE `file_shares`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `folders`
--
ALTER TABLE `folders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_settings`
--
ALTER TABLE `user_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `files_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `file_shares`
--
ALTER TABLE `file_shares`
  ADD CONSTRAINT `file_shares_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `folders`
--
ALTER TABLE `folders`
  ADD CONSTRAINT `folders_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD CONSTRAINT `user_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
