SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `rth` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `rth`;

CREATE TABLE `acp_perms` (
  `id` bigint(20) NOT NULL,
  `dcid` bigint(20) NOT NULL,
  `add_tt_access` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `api` (
  `id` bigint(20) NOT NULL,
  `lifetime` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Is token lifetime | 1 = true | 0 = false',
  `last_payment` date NOT NULL DEFAULT current_timestamp(),
  `tariff` varchar(5) NOT NULL DEFAULT 'u1',
  `dcid` bigint(20) NOT NULL,
  `rttoken` varchar(58) NOT NULL DEFAULT 'RTToken-Default',
  `secret` varchar(128) NOT NULL COMMENT 'SHA-512 Hash'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `api_coins` (
  `id` bigint(20) NOT NULL,
  `rttoken` varchar(58) NOT NULL,
  `dcid` bigint(20) NOT NULL,
  `api_coins` bigint(20) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `api_packs` (
  `id` bigint(20) NOT NULL,
  `name` varchar(200) NOT NULL,
  `start_coins` bigint(20) NOT NULL,
  `tariff` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `api_packs` (`id`, `name`, `start_coins`, `tariff`) VALUES
(1, 'standard', 500, 'u1'),
(2, 'premium', 1000, 'u2'),
(3, 'staff', 5000, 's1');

CREATE TABLE `api_tariffs` (
  `id` bigint(20) NOT NULL,
  `tariff` varchar(5) NOT NULL,
  `cpr` tinyint(2) NOT NULL COMMENT 'Coins Per Request',
  `tariff_name` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `api_tariffs` (`id`, `tariff`, `cpr`, `tariff_name`) VALUES
(1, 'u1', 5, 'standard'),
(2, 'u2', 2, 'premium'),
(3, 's1', 1, 'staff tariff'),
(4, 's2', 0, 'high staff tariff');

CREATE TABLE `coinsys` (
  `id` bigint(20) NOT NULL,
  `dcid` bigint(20) NOT NULL,
  `coins` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

CREATE TABLE `teamtasks_access` (
  `id` bigint(20) NOT NULL,
  `dcid` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `acp_perms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dcid` (`dcid`);

ALTER TABLE `api`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dcid` (`dcid`),
  ADD UNIQUE KEY `rttoken` (`rttoken`),
  ADD UNIQUE KEY `secret` (`secret`);

ALTER TABLE `api_coins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rttoken` (`rttoken`),
  ADD UNIQUE KEY `dcid` (`dcid`);

ALTER TABLE `api_packs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `api_tariffs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tariff` (`tariff`);

ALTER TABLE `coinsys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dcid` (`dcid`);

ALTER TABLE `prices`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `servers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vmid` (`vmid`,`dcid`);

ALTER TABLE `teamtasks`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `teamtasks_access`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dcid` (`dcid`);


ALTER TABLE `acp_perms`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `api`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `api_coins`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `api_packs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `api_tariffs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `coinsys`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `prices`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `servers`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'NO CHANGES!';

ALTER TABLE `teamtasks`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `teamtasks_access`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
