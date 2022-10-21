-- phpMyAdmin SQL Dump
-- version 5.0.4deb2+deb11u1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 21, 2022 at 01:14 AM
-- Server version: 8.0.30
-- PHP Version: 7.4.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `upldf`
--

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `file_row_id` smallint UNSIGNED NOT NULL,
  `file_id` char(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `file_name` tinytext NOT NULL,
  `file_date` char(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '000000',
  `file_size` int UNSIGNED NOT NULL,
  `file_password` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `file_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `file_user_id` smallint UNSIGNED DEFAULT NULL,
  `file_user_ip` varchar(39) NOT NULL,
  `file_virus_scanned` bit(1) NOT NULL DEFAULT b'0',
  `file_virus_found` bit(1) DEFAULT NULL,
  `file_virus_signature` tinytext,
  `file_downloads` smallint UNSIGNED NOT NULL DEFAULT '0',
  `file_removed` bit(1) DEFAULT b'0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` smallint UNSIGNED NOT NULL,
  `report_file_id` char(5) NOT NULL,
  `report_user_id` smallint UNSIGNED DEFAULT NULL,
  `report_user_ip` varchar(39) NOT NULL,
  `report_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` smallint UNSIGNED NOT NULL,
  `user_email` varchar(319) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `user_email_verification` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `user_email_verification_sent` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_email_previous` varchar(319) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `user_password` tinytext NOT NULL,
  `user_passreset_verification` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `user_passreset_expiry` timestamp NULL DEFAULT NULL,
  `user_reg_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_reg_ip` varchar(39) NOT NULL,
  `user_verified` bit(1) NOT NULL DEFAULT b'0',
  `user_failed_logins` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `user_last_failed_login` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_banned` bit(1) NOT NULL DEFAULT b'0',
  `user_closed` bit(1) NOT NULL DEFAULT b'0',
  `user_admin` bit(1) NOT NULL DEFAULT b'0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`file_row_id`),
  ADD UNIQUE KEY `file_link` (`file_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD UNIQUE KEY `report_file_id` (`report_file_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `file_row_id` smallint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` smallint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` smallint UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
