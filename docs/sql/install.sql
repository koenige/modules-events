/**
 * Zugzwang Project
 * SQL for installation of events module
 *
 * http://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */

CREATE TABLE `events` (
  `event_id` int unsigned NOT NULL AUTO_INCREMENT,
  `event` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `identifier` varchar(63) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `abstract` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `registration` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `event_category_id` int unsigned NOT NULL,
  `date_begin` date NOT NULL,
  `date_end` date DEFAULT NULL,
  `time_begin` time DEFAULT NULL,
  `time_end` time DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `direct_link` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs DEFAULT NULL,
  `published` enum('yes','no') CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL DEFAULT 'no',
  `takes_place` enum('yes','no') CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL DEFAULT 'yes',
  `show_in_news` enum('yes','no') CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL DEFAULT 'no',
  `following` enum('yes','no') CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL DEFAULT 'no',
  `sequence` tinyint unsigned DEFAULT NULL,
  `main_event_id` int unsigned DEFAULT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`event_id`),
  UNIQUE KEY `identifier` (`identifier`),
  KEY `event_category_id` (`event_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'categories', 'category_id', (SELECT DATABASE()), 'events', 'event_id', 'event_category_id', 'no-delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'events', 'event_id', (SELECT DATABASE()), 'events', 'event_id', 'main_event_id', 'no-delete');

INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Events', NULL, NULL, 'events', NULL, NULL, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Place', NULL, (SELECT category_id FROM categories WHERE identifier = 'contact'), 'contact/place', NULL, NULL, NOW());

CREATE TABLE `events_categories` (
  `event_category_id` int unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int unsigned NOT NULL,
  `category_id` int unsigned NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`event_category_id`),
  UNIQUE KEY `date_id_category_id` (`event_id`,`category_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'categories', 'category_id', (SELECT DATABASE()), 'events_categories', 'event_category_id', 'category_id', 'no-delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'events', 'event_id', (SELECT DATABASE()), 'events_categories', 'event_category_id', 'event_id', 'delete');

CREATE TABLE `events_contacts` (
  `event_contact_id` int unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int unsigned NOT NULL,
  `contact_id` int unsigned NOT NULL,
  `role_category_id` int unsigned NOT NULL,
  `sequence` tinyint unsigned NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`event_contact_id`),
  UNIQUE KEY `event_id` (`event_id`,`contact_id`,`role_category_id`),
  KEY `contact_id` (`contact_id`),
  KEY `role_category_id` (`role_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'contacts', 'contact_id', (SELECT DATABASE()), 'events_contacts', 'event_contact_id', 'contact_id', 'no-delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'events ', 'event_id', (SELECT DATABASE()), 'events_contacts ', 'event_contact_id', 'event_id', 'delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'categories ', 'category_id', (SELECT DATABASE()), 'events_contacts ', 'event_contact_id', 'role_category_id', 'no-delete');

INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Roles', NULL, NULL, 'roles', NULL, NULL, NOW());

CREATE TABLE `events_media` (
  `event_medium_id` int unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int unsigned NOT NULL,
  `medium_id` int unsigned NOT NULL,
  `sequence` tinyint unsigned NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`event_medium_id`),
  UNIQUE KEY `event_medium` (`event_id`,`medium_id`),
  KEY `medium_id` (`medium_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'media ', 'medium_id', (SELECT DATABASE()), 'events_media ', 'event_medium_id', 'medium_id', 'no-delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'events ', 'event_id', (SELECT DATABASE()), 'events_media ', 'event_medium_id', 'event_id', 'delete');

INSERT INTO webpages (`title`, `content`, `identifier`, `ending`, `sequence`, `mother_page_id`, `live`, `menu`, `last_update`) VALUES ("iCalendar", "%%% request ics %%%", "/ics*", "none", "30", "1", "yes", NULL, NOW());
INSERT INTO webpages (`title`, `content`, `identifier`, `ending`, `sequence`, `mother_page_id`, `live`, `menu`, `last_update`) VALUES ('Events', '%%% request events %%%', '/events', '/', 20, (SELECT page_id FROM webpages wp WHERE identifier = '/'), 'yes', NULL, NOW());
INSERT INTO webpages (`title`, `content`, `identifier`, `ending`, `sequence`, `mother_page_id`, `live`, `menu`, `last_update`) VALUES ('Events', '%%% request events * %%%', '/events*', '/', 1, (SELECT page_id FROM webpages wp WHERE identifier = '/events'), 'yes', NULL, NOW());
