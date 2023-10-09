-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 09, 2023 at 05:59 PM
-- Server version: 10.3.39-MariaDB-0+deb10u1
-- PHP Version: 8.1.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rth`
--

-- --------------------------------------------------------

--
-- Table structure for table `api`
--
-- Creation: Oct 09, 2023 at 02:25 PM
-- Last update: Oct 09, 2023 at 03:26 PM
--

CREATE TABLE `api` (
  `id` bigint(20) NOT NULL,
  `tariff` varchar(5) NOT NULL DEFAULT 'u1',
  `dcid` bigint(20) NOT NULL,
  `rttoken` varchar(58) NOT NULL DEFAULT 'RTToken-Default',
  `secret` varchar(128) NOT NULL COMMENT 'SHA-512 Hash'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `api_tariffs`
--
-- Creation: Oct 09, 2023 at 02:26 PM
-- Last update: Oct 09, 2023 at 02:33 PM
--

CREATE TABLE `api_tariffs` (
  `id` bigint(20) NOT NULL,
  `tariff` varchar(5) NOT NULL,
  `cpr` tinyint(2) NOT NULL COMMENT 'Coins Per Request',
  `tariff_name` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `api_tariffs`
--

INSERT INTO `api_tariffs` (`id`, `tariff`, `cpr`, `tariff_name`) VALUES
(1, 'u1', 5, NULL),
(2, 'u2', 2, NULL),
(3, 's1', 1, 'staff tariff'),
(4, 's2', 0, 'high staff tariff');

-- --------------------------------------------------------

--
-- Table structure for table `api_coins`
--
-- Creation: Oct 08, 2023 at 04:45 PM
-- Last update: Oct 09, 2023 at 03:56 PM
--

CREATE TABLE `api_coins` (
  `id` bigint(20) NOT NULL,
  `dcid` bigint(20) NOT NULL,
  `coins` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prices`
--
-- Creation: Oct 09, 2023 at 12:50 AM
-- Last update: Oct 08, 2023 at 06:58 PM
--

CREATE TABLE `prices` (
  `id` bigint(20) NOT NULL,
  `root` tinyint(1) NOT NULL COMMENT 'kvm | 1 = true | 0 = false | Renaming without changing the backend will result in fatal errors',
  `pack` varchar(5000) NOT NULL COMMENT 'Name of the Pack',
  `cores` int(11) NOT NULL,
  `ram` int(11) NOT NULL,
  `storage` int(11) NOT NULL,
  `traffic` varchar(500) NOT NULL,
  `price` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prices`
--

INSERT INTO `prices` (`id`, `root`, `pack`, `cores`, `ram`, `storage`, `traffic`, `price`) VALUES
(1, 1, 'Beginner', 1, 3, 30, 'Unlimited', 5),
(2, 1, 'Web-Hosting', 1, 2, 5, 'Unlimited', 2),
(3, 1, 'Game-Server', 3, 15, 70, 'Unlimited', 10),
(4, 0, 'Beginner', 2, 5, 30, 'Unlimited', 5),
(5, 0, 'Web-Hosting', 1, 3, 5, 'Unlimited', 2),
(7, 0, 'Game-Server', 4, 18, 70, 'Unlimited', 10),
(9, 1, 'Ultra', 6, 35, 250, 'Unlimited', 45),
(11, 0, 'Ultra', 7, 37, 250, 'Unlimited', 45),
(12, 1, 'Custom / Please contact the Support.', 0, 0, 0, 'Unlimited', 0),
(13, 0, 'Custom / Please contact the Support.', 0, 0, 0, 'Unlimited', 0);

-- --------------------------------------------------------

--
-- Table structure for table `servers`
--
-- Creation: Oct 09, 2023 at 12:48 AM
--

CREATE TABLE `servers` (
  `id` bigint(20) NOT NULL COMMENT 'NO CHANGES!',
  `dcid` bigint(20) NOT NULL,
  `vmid` bigint(20) NOT NULL,
  `kvm` tinyint(1) NOT NULL COMMENT 'Is kvm | 1 = true | 0 = false',
  `done` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Payment done | 1 = true | 0 = false',
  `pack` varchar(5000) NOT NULL DEFAULT 'Pack not set',
  `name` varchar(5000) NOT NULL DEFAULT 'Not set'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `api`
--
ALTER TABLE `api`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rttoken` (`rttoken`,`secret`),
  ADD UNIQUE KEY `dcid` (`dcid`);

--
-- Indexes for table `api_tariffs`
--
ALTER TABLE `api_tariffs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tariff` (`tariff`);

--
-- Indexes for table `api_coins`
--
ALTER TABLE `api_coins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dcid` (`dcid`);

--
-- Indexes for table `prices`
--
ALTER TABLE `prices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `servers`
--
ALTER TABLE `servers`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `api`
--
ALTER TABLE `api`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `api_tariffs`
--
ALTER TABLE `api_tariffs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `api_coins`
--
ALTER TABLE `api_coins`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prices`
--
ALTER TABLE `prices`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `servers`
--
ALTER TABLE `servers`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'NO CHANGES!';
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
