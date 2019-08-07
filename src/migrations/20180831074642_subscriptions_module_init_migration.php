<?php

use Phinx\Migration\AbstractMigration;

class SubscriptionsModuleInitMigration extends AbstractMigration
{
    public function up()
    {
        $sql = <<<SQL
SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';


CREATE TABLE IF NOT EXISTS `subscription_extension_methods` (
  `method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `sorting` int(11) NOT NULL DEFAULT '100',
  PRIMARY KEY (`method`),
  KEY `sorting` (`sorting`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `subscription_length_methods` (
  `method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `sorting` int(11) NOT NULL DEFAULT '100',
  PRIMARY KEY (`method`),
  KEY `sorting` (`sorting`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `subscription_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'time_archive',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extension_method_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'extend_actual',
  `length_method_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fix_days',
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `length` int(11) NOT NULL,
  `extending_length` int(11) DEFAULT NULL,
  `fixed_end` datetime DEFAULT NULL,
  `user_label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `web` tinyint(1) NOT NULL DEFAULT '1',
  `print` tinyint(1) NOT NULL DEFAULT '0',
  `club` tinyint(1) NOT NULL DEFAULT '0',
  `mobile` tinyint(1) NOT NULL DEFAULT '0',
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `sorting` int(11) NOT NULL DEFAULT '10',
  `created_at` datetime NOT NULL,
  `modified_at` datetime NOT NULL,
  `next_subscription_type_id` int(11) DEFAULT NULL,
  `no_subscription` tinyint(1) NOT NULL DEFAULT '0',
  `price_backup` float DEFAULT NULL,
  `print_friday` tinyint(1) NOT NULL DEFAULT '0',
  `ad_free` tinyint(1) NOT NULL DEFAULT '0',
  `ask_address` tinyint(1) NOT NULL DEFAULT '0',
  `limit_per_user` int(11) DEFAULT NULL,
  `disable_notifications` tinyint(1) NOT NULL DEFAULT '0',
  `recurrent_charge_before` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sorting` (`sorting`),
  KEY `code` (`code`),
  KEY `next_subscription_type_id` (`next_subscription_type_id`),
  KEY `extension_method_id` (`extension_method_id`),
  KEY `length_method_id` (`length_method_id`),
  CONSTRAINT `subscription_types_ibfk_1` FOREIGN KEY (`next_subscription_type_id`) REFERENCES `subscription_types` (`id`),
  CONSTRAINT `subscription_types_ibfk_2` FOREIGN KEY (`extension_method_id`) REFERENCES `subscription_extension_methods` (`method`),
  CONSTRAINT `subscription_types_ibfk_3` FOREIGN KEY (`length_method_id`) REFERENCES `subscription_length_methods` (`method`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `subscription_type_names` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sorting` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`type`),
  KEY `sorting` (`sorting`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `subscription_type_id` int(11) NOT NULL,
  `is_recurrent` tinyint(1) NOT NULL DEFAULT '0',
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `length` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `modified_at` datetime NOT NULL,
  `next_subscription_id` int(11) DEFAULT NULL,
  `internal_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown',
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `start_time` (`start_time`,`end_time`),
  KEY `subscription_type_id` (`subscription_type_id`),
  KEY `user_id` (`user_id`),
  KEY `next_subscription_id` (`next_subscription_id`),
  KEY `created_at` (`created_at`),
  KEY `start_time_2` (`start_time`),
  KEY `end_time` (`end_time`),
  KEY `address_id` (`address_id`),
  KEY `type` (`type`),
  CONSTRAINT `subscriptions_ibfk_2` FOREIGN KEY (`subscription_type_id`) REFERENCES `subscription_types` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `subscriptions_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `subscriptions_ibfk_4` FOREIGN KEY (`next_subscription_id`) REFERENCES `subscriptions` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `subscriptions_ibfk_6` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`),
  CONSTRAINT `subscriptions_ibfk_7` FOREIGN KEY (`type`) REFERENCES `subscription_type_names` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2018-08-31 07:42:42
SQL;
        $this->execute($sql);
    }

    public function down()
    {
        // TODO: [refactoring] add down migrations for module init migrations
        $this->output->writeln('Down migration is not available.');
    }
}
