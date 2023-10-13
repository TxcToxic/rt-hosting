-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 14, 2023 at 01:06 AM
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
-- Table structure for table `acp_perms`
--

CREATE TABLE `acp_perms` (
  `id` bigint(20) NOT NULL,
  `dcid` bigint(20) NOT NULL,
  `add_tt_access` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `acp_perms`
--

INSERT INTO `acp_perms` (`id`, `dcid`, `add_tt_access`) VALUES
(2, 812393175427448843, 1),
(3, 856594604812009502, 1),
(4, 370986466555199490, 0);

-- --------------------------------------------------------

--
-- Table structure for table `api`
--

CREATE TABLE `api` (
  `id` bigint(20) NOT NULL,
  `lifetime` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Is token lifetime | 1 = true | 0 = false',
  `last_payment` date NOT NULL DEFAULT current_timestamp(),
  `tariff` varchar(5) NOT NULL DEFAULT 'u1',
  `dcid` bigint(20) NOT NULL,
  `rttoken` varchar(58) NOT NULL DEFAULT 'RTToken-Default',
  `secret` varchar(128) NOT NULL COMMENT 'SHA-512 Hash'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `api`
--

INSERT INTO `api` (`id`, `lifetime`, `last_payment`, `tariff`, `dcid`, `rttoken`, `secret`) VALUES
(1, 1, '2023-10-09', 's2', 856594604812009502, 'RTToken-gjbXEyq3fu9sCme8Om7nojKr6G6zOrzxAdi3KhKjFQd5oBCF7p', 'e77a25b656d1ae8fe217cbc7d4b91bc98e7bace4d77932ef0cda1aa7358c6b7f65ec0354d835975f9a805f99296bb8c5c0a9f90198b6cf0b3edf3ff4d706dc7d'),
(2, 1, '2023-10-09', 's2', 812393175427448843, 'RTToken-mAsyTdtG0dLl7eN79BafQ1a2uhutdFY49sk77owfj46YuyrOii', '1ad1b2723b7663aa8b7a9ab29259d28ad9f9a47fa87d47a16bce120d17f27894144ee6a6ca5df47e38a10608b973c08173b17fbcf866841a235ea2338e53f833'),
(8, 1, '2023-10-13', 's2', 370986466555199490, 'RTToken-hWKYYmXd3kVUds147ledRtH8TvqlTnuXfNIDKEHhoSSJE8vsYi', '9edf0b0e66cb8c73d06704b9c843624bc33d01e9e1bd877cd308d1985a2a624744bb07fbadbadcbb6bbf4623b64b86d5ab122ebb7ce87feb8aa87c13fa89aadb');

-- --------------------------------------------------------

--
-- Table structure for table `api_coins`
--

CREATE TABLE `api_coins` (
  `id` bigint(20) NOT NULL,
  `rttoken` varchar(58) NOT NULL,
  `dcid` bigint(20) NOT NULL,
  `api_coins` bigint(20) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `api_coins`
--

INSERT INTO `api_coins` (`id`, `rttoken`, `dcid`, `api_coins`) VALUES
(3, 'RTToken-hWKYYmXd3kVUds147ledRtH8TvqlTnuXfNIDKEHhoSSJE8vsYi', 370986466555199490, 500014);

-- --------------------------------------------------------

--
-- Table structure for table `api_packs`
--

CREATE TABLE `api_packs` (
  `id` bigint(20) NOT NULL,
  `name` varchar(200) NOT NULL,
  `start_coins` bigint(20) NOT NULL,
  `tariff` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `api_packs`
--

INSERT INTO `api_packs` (`id`, `name`, `start_coins`, `tariff`) VALUES
(1, 'standard', 500, 'u1'),
(2, 'premium', 1000, 'u2'),
(3, 'staff', 5000, 's1');

-- --------------------------------------------------------

--
-- Table structure for table `api_tariffs`
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
(1, 'u1', 5, 'standard'),
(2, 'u2', 2, 'premium'),
(3, 's1', 1, 'staff tariff'),
(4, 's2', 0, 'high staff tariff');

-- --------------------------------------------------------

--
-- Table structure for table `coinsys`
--

CREATE TABLE `coinsys` (
  `id` bigint(20) NOT NULL,
  `dcid` bigint(20) NOT NULL,
  `coins` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coinsys`
--

INSERT INTO `coinsys` (`id`, `dcid`, `coins`) VALUES
(6, 856594604812009502, 442),
(7, 812393175427448843, 221),
(8, 1145338817931386911, 9),
(9, 1160354393896529940, 18),
(10, 1103970877718155285, 16),
(11, 370986466555199490, 9),
(15, 942171405104054362, 314),
(16, 1113190746678378496, 5);

-- --------------------------------------------------------

--
-- Table structure for table `prices`
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

CREATE TABLE `servers` (
  `id` bigint(20) NOT NULL COMMENT 'NO CHANGES!',
  `dcid` bigint(20) NOT NULL,
  `vmid` bigint(20) NOT NULL,
  `last_payment` date NOT NULL DEFAULT current_timestamp(),
  `ignore_payment` tinyint(1) NOT NULL DEFAULT 0,
  `kvm` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Is kvm | 1 = true | 0 = false',
  `done` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Payment done | 1 = true | 0 = false',
  `pack` varchar(5000) NOT NULL DEFAULT 'Pack not set',
  `name` varchar(5000) NOT NULL DEFAULT 'Not set'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `servers`
--

INSERT INTO `servers` (`id`, `dcid`, `vmid`, `last_payment`, `ignore_payment`, `kvm`, `done`, `pack`, `name`) VALUES
(1, 856594604812009502, 105, '2023-09-09', 0, 0, 1, 'Penis Pack 1337', 'toxic1835 test machine'),
(3, 812393175427448843, 106, '2023-10-09', 1, 0, 1, 'LG Windows-Server', ''),
(4, 812393175427448843, 100, '2023-10-09', 1, 0, 1, 'LG Gameserver', ''),
(5, 812393175427448843, 107, '2023-10-09', 1, 0, 1, 'LG Web-Server', ''),
(7, 856594604812009502, 104, '2023-10-09', 1, 0, 1, 'RT-Hosting Machine', 'RT-Hosting Main Server'),
(8, 812393175427448843, 103, '2023-10-09', 1, 0, 1, 'LG Pterodactyl-Server', ''),
(9, 812393175427448843, 104, '2023-10-09', 1, 0, 1, 'RT-Hosting Machine', 'RT-Hosting Main Server'),
(11, 942171405104054362, 104, '2023-10-10', 1, 0, 1, 'RT-Hosting Machine', 'RT-Hosting Main Server');

-- --------------------------------------------------------

--
-- Table structure for table `teamtasks`
--

CREATE TABLE `teamtasks` (
  `id` bigint(20) NOT NULL,
  `dcid` bigint(20) NOT NULL,
  `task_title` varchar(200) NOT NULL DEFAULT 'Not Set',
  `task` longtext NOT NULL,
  `done` tinyint(1) NOT NULL DEFAULT 0,
  `creation` datetime NOT NULL DEFAULT current_timestamp(),
  `creator_dcid` bigint(20) NOT NULL,
  `deadline` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teamtasks`
--

INSERT INTO `teamtasks` (`id`, `dcid`, `task_title`, `task`, `done`, `creation`, `creator_dcid`, `deadline`) VALUES
(1, 370986466555199490, 'RT-Hosting (Main Bot)', 'RT-Hosting (Main Bot) - `/convert` tausche normale coins zu api_coins um | 2 Normale Coins = 1 API Coin\n\n/convert api-coins: x\nx stellt in diesem Szenario eine Ganzzahl dar (integer)', 1, '2023-10-13 16:43:12', 856594604812009502, '2023-10-13 23:00:00'),
(2, 370986466555199490, 'RT-Hosting (Main Bot)', 'alle admin commands (coins / api-coins) überprüfen & überarbeiten.\n\nVorschrift:\nMan darf nicht unter 0 kommen egal wie, egal welcher command, egal welcher coin', 0, '2023-10-13 18:14:11', 856594604812009502, '2023-10-14 23:00:00'),
(3, 856594604812009502, 'Arschloch', 'Arschloch', 1, '2023-10-13 22:01:42', 812393175427448843, '2023-10-14 15:00:00'),
(4, 370986466555199490, 'Neues Logo', 'Neues Logo', 0, '2023-10-13 22:15:30', 812393175427448843, '2023-10-15 23:59:00'),
(6, 812393175427448843, 'RT-Hosting (Main Bot) Set / Add / Remove / Coins', 'Überprüfen ob der user schon in der Datenbank steht (api sowohl normale) wenn user noch nicht exestiert fehler ausgeben', 1, '2023-10-13 22:33:47', 856594604812009502, '2023-10-14 20:00:00'),
(7, 370986466555199490, 'RT-Hosting (Main Bot)', 'Ignore Bots from grinding coins', 0, '2023-10-14 00:12:57', 856594604812009502, '2023-10-14 16:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `teamtasks_access`
--

CREATE TABLE `teamtasks_access` (
  `id` bigint(20) NOT NULL,
  `dcid` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teamtasks_access`
--

INSERT INTO `teamtasks_access` (`id`, `dcid`) VALUES
(7, 370986466555199490),
(12, 812393175427448843),
(1, 856594604812009502),
(6, 1103970877718155285),
(3, 1113190746678378496),
(8, 1145338817931386911);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `acp_perms`
--
ALTER TABLE `acp_perms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dcid` (`dcid`);

--
-- Indexes for table `api`
--
ALTER TABLE `api`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dcid` (`dcid`),
  ADD UNIQUE KEY `rttoken` (`rttoken`),
  ADD UNIQUE KEY `secret` (`secret`);

--
-- Indexes for table `api_coins`
--
ALTER TABLE `api_coins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rttoken` (`rttoken`),
  ADD UNIQUE KEY `dcid` (`dcid`);

--
-- Indexes for table `api_packs`
--
ALTER TABLE `api_packs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `api_tariffs`
--
ALTER TABLE `api_tariffs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tariff` (`tariff`);

--
-- Indexes for table `coinsys`
--
ALTER TABLE `coinsys`
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
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vmid` (`vmid`,`dcid`);

--
-- Indexes for table `teamtasks`
--
ALTER TABLE `teamtasks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teamtasks_access`
--
ALTER TABLE `teamtasks_access`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dcid` (`dcid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `acp_perms`
--
ALTER TABLE `acp_perms`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `api`
--
ALTER TABLE `api`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `api_coins`
--
ALTER TABLE `api_coins`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `api_packs`
--
ALTER TABLE `api_packs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `api_tariffs`
--
ALTER TABLE `api_tariffs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `coinsys`
--
ALTER TABLE `coinsys`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `prices`
--
ALTER TABLE `prices`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `servers`
--
ALTER TABLE `servers`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'NO CHANGES!', AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `teamtasks`
--
ALTER TABLE `teamtasks`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `teamtasks_access`
--
ALTER TABLE `teamtasks_access`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
