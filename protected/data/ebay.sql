-- --------------------------------------------------------
-- Host:                         localhost
-- Server version:               5.1.49-3 - (Debian)
-- Server OS:                    debian-linux-gnu
-- HeidiSQL Version:             9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping database structure for esl
CREATE DATABASE IF NOT EXISTS `esl` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `esl`;


-- Dumping structure for table esl.configs
CREATE TABLE IF NOT EXISTS `configs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` longtext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table esl.listing_items
CREATE TABLE IF NOT EXISTS `listing_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ebay_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `url_picture` varchar(255) NOT NULL DEFAULT '',
  `url_item` varchar(255) NOT NULL DEFAULT '',
  `buy_it_now` tinyint(4) NOT NULL DEFAULT '0',
  `date_start` int(10) unsigned NOT NULL DEFAULT '0',
  `date_end` int(10) unsigned NOT NULL DEFAULT '0',
  `currency` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ebay_id` (`ebay_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table esl.listing_names
CREATE TABLE IF NOT EXISTS `listing_names` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `date_create` int(10) unsigned NOT NULL DEFAULT '0',
  `date_update` int(10) unsigned NOT NULL DEFAULT '0',
  `ignored` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table esl.listing_names_items
CREATE TABLE IF NOT EXISTS `listing_names_items` (
  `listing_name_id` int(10) unsigned NOT NULL,
  `listing_item_id` int(10) unsigned NOT NULL,
  `date_add` int(10) unsigned NOT NULL,
  PRIMARY KEY (`listing_name_id`,`listing_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table esl.search_items
CREATE TABLE IF NOT EXISTS `search_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ebay_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `url_picture` varchar(255) NOT NULL,
  `url_item` varchar(255) NOT NULL,
  `buy_it_now` tinyint(4) NOT NULL DEFAULT '0',
  `date_of_added` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ebay_id` (`ebay_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table esl.search_requests
CREATE TABLE IF NOT EXISTS `search_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `date_update` int(10) unsigned NOT NULL,
  `request_name` varchar(255) NOT NULL,
  `end_time_from` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'minutes',
  `price_min` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `price_max` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `auction_type_id` int(11) NOT NULL,
  `condition` int(11) NOT NULL DEFAULT '0',
  `ebay_category_id` int(11) NOT NULL DEFAULT '0',
  `keyword` varchar(255) NOT NULL,
  `lots_count` int(10) unsigned NOT NULL DEFAULT '100',
  `ignore_list` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ebay_global_id` varchar(16) NOT NULL DEFAULT 'EBAY-US',
  `only_new` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table esl.search_requests_items
CREATE TABLE IF NOT EXISTS `search_requests_items` (
  `search_item_id` int(10) unsigned NOT NULL,
  `search_request_id` int(10) unsigned NOT NULL,
  `date_update` int(10) unsigned NOT NULL,
  PRIMARY KEY (`search_item_id`,`search_request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table esl.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(60) NOT NULL,
  `date_last_visit` int(11) NOT NULL DEFAULT '0',
  `email_verified` tinyint(4) NOT NULL DEFAULT '0',
  `url_maintenance` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
