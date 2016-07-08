CREATE TABLE IF NOT EXISTS `access_tokens` (
  `client_config_id` varchar(255) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `scope` varchar(255) NOT NULL,
  `issue_time` int(11) NOT NULL,
  `access_token` text NOT NULL,
  `token_type` varchar(255) NOT NULL,
  `expires_in` int(11) DEFAULT NULL,
  UNIQUE KEY `client_config_id` (`client_config_id`,`user_id`,`scope`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `refresh_tokens` (
  `client_config_id` varchar(255) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `scope` varchar(255) NOT NULL,
  `issue_time` int(11) NOT NULL,
  `refresh_token` text,
  UNIQUE KEY `client_config_id` (`client_config_id`,`user_id`,`scope`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `states` (
  `client_config_id` varchar(255) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `scope` varchar(255) NOT NULL,
  `issue_time` int(11) NOT NULL,
  `state` varchar(255) NOT NULL,
  PRIMARY KEY (`state`),
  UNIQUE KEY `client_config_id` (`client_config_id`,`user_id`,`scope`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `stats` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_uuid` varchar(36) DEFAULT NULL,
  `totalTrips` int(11) DEFAULT NULL,
  `totalKm` decimal(10,2) DEFAULT NULL,
  `totalWaitTime` int(8) DEFAULT NULL,
  `totalTime` int(8) DEFAULT NULL,
  `cities` text,
  `products` text,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_uuid` (`user_uuid`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) CHARACTER SET utf8 DEFAULT NULL,
  `first_name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `last_name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `email` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `session_id` varchar(36) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uuid` (`uuid`)
) ENGINE=InnoDB;