SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `rth` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `rth`;

CREATE TABLE IF NOT EXISTS `api` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `lifetime` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Is token lifetime | 1 = true | 0 = false',
  `last_payment` date NOT NULL DEFAULT current_timestamp(),
  `tariff` varchar(5) NOT NULL DEFAULT 'u1',
  `dcid` bigint(20) NOT NULL,
  `rttoken` varchar(58) NOT NULL DEFAULT 'RTToken-Default',
  `secret` varchar(128) NOT NULL COMMENT 'SHA-512 Hash',
  PRIMARY KEY (`id`),
  UNIQUE KEY `dcid` (`dcid`),
  UNIQUE KEY `rttoken` (`rttoken`),
  UNIQUE KEY `secret` (`secret`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `api_coins` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `rttoken` varchar(58) NOT NULL,
  `dcid` bigint(20) NOT NULL,
  `api_coins` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rttoken` (`rttoken`),
  UNIQUE KEY `dcid` (`dcid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `api_tariffs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tariff` varchar(5) NOT NULL,
  `cpr` tinyint(2) NOT NULL COMMENT 'Coins Per Request',
  `tariff_name` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tariff` (`tariff`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `api_tariffs` (`id`, `tariff`, `cpr`, `tariff_name`) VALUES
(1, 'u1', 5, 'standard'),
(2, 'u2', 2, 'premium'),
(3, 's1', 1, 'staff tariff'),
(4, 's2', 0, 'high staff tariff');

CREATE TABLE IF NOT EXISTS `coinsys` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `dcid` bigint(20) NOT NULL,
  `coins` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dcid` (`dcid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `prices` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `root` tinyint(1) NOT NULL COMMENT 'kvm | 1 = true | 0 = false | Renaming without changing the backend will result in fatal errors',
  `pack` varchar(5000) NOT NULL COMMENT 'Name of the Pack',
  `cores` int(11) NOT NULL,
  `ram` int(11) NOT NULL,
  `storage` int(11) NOT NULL,
  `traffic` varchar(500) NOT NULL,
  `price` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
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

CREATE TABLE IF NOT EXISTS `servers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'NO CHANGES!',
  `dcid` bigint(20) NOT NULL,
  `vmid` bigint(20) NOT NULL,
  `last_payment` date NOT NULL DEFAULT current_timestamp(),
  `ignore_payment` tinyint(1) NOT NULL DEFAULT 0,
  `kvm` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Is kvm | 1 = true | 0 = false',
  `done` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Payment done | 1 = true | 0 = false',
  `pack` varchar(5000) NOT NULL DEFAULT 'Pack not set',
  `name` varchar(5000) NOT NULL DEFAULT 'Not set',
  PRIMARY KEY (`id`),
  UNIQUE KEY `vmid` (`vmid`,`dcid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `teamtasks` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `dcid` bigint(20) NOT NULL,
  `task_title` varchar(200) NOT NULL DEFAULT 'Not Set',
  `task` longtext NOT NULL,
  `done` tinyint(1) NOT NULL DEFAULT 0,
  `creation` datetime NOT NULL DEFAULT current_timestamp(),
  `creator_dcid` bigint(20) NOT NULL,
  `deadline` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `teamtasks_access` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `dcid` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
