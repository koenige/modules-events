/**
 * events module
 * SQL updates 
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020-2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */

/* 2020-05-16-1 */	ALTER TABLE `events` CHANGE `place` `place_contact_id` int unsigned NOT NULL AFTER `registration`, CHANGE `event_category_id` `event_category_id` int unsigned NOT NULL AFTER `place_contact_id`, CHANGE `date_begin` `date_begin` date NOT NULL AFTER `event_category_id`, CHANGE `date_end` `date_end` date NULL AFTER `date_begin`, CHANGE `time_begin` `time_begin` time NULL AFTER `date_end`, CHANGE `time_end` `time_end` time NULL AFTER `time_begin`, CHANGE `author_person_id` `author_person_id` int unsigned NULL AFTER `time_end`, CHANGE `date_created` `created` datetime NULL AFTER `author_person_id`, DROP `time_created`, CHANGE `direct_link` `direct_link` varchar(255) COLLATE 'latin1_general_cs' NULL AFTER `created`, CHANGE `published` `published` enum('yes','no') COLLATE 'latin1_general_cs' NOT NULL DEFAULT 'no' AFTER `direct_link`, CHANGE `takes_place` `takes_place` enum('yes','no') COLLATE 'latin1_general_cs' NOT NULL DEFAULT 'yes' AFTER `published`, CHANGE `show_in_news` `show_in_news` enum('yes','no') COLLATE 'latin1_general_cs' NOT NULL DEFAULT 'no' AFTER `takes_place`, CHANGE `following` `following` enum('yes','no') COLLATE 'latin1_general_cs' NOT NULL DEFAULT 'no' AFTER `show_in_news`, CHANGE `sequence` `sequence` tinyint unsigned NULL AFTER `following`, CHANGE `main_event_id` `main_event_id` int unsigned NULL AFTER `sequence`;
/* 2020-05-16-2 */	ALTER TABLE `events_categories` CHANGE `ec_id` `event_category_id` int unsigned NOT NULL AUTO_INCREMENT FIRST;
/* 2020-05-16-3 */	ALTER TABLE `events_categories` ADD INDEX `category_id` (`category_id`);
/* 2020-05-16-4 */	ALTER TABLE `events_contacts` ADD `sequence` tinyint unsigned NULL AFTER `role_category_id`;
/* 2020-05-16-5 */	ALTER TABLE `events_media` CHANGE `sequence` `sequence` tinyint unsigned NOT NULL AFTER `medium_id`;
/* 2020-05-16-6 */	DELETE FROM _relations WHERE `detail_db` = (SELECT DATABASE()) AND `detail_table` = 'events' AND `detail_id_field` = 'event_id' AND `detail_field` = 'place_contact_id';
/* 2020-05-16-7 */	DELETE FROM _relations WHERE `detail_db` = (SELECT DATABASE()) AND `detail_table` = 'events' AND `detail_id_field` = 'event_id' AND `detail_field` = 'author_person_id';
/* 2020-05-16-8 */	ALTER TABLE `events` DROP `place_contact_id`, DROP `author_person_id`;
/* 2020-05-16-9 */	ALTER TABLE `events` ADD INDEX `event_category_id` (`event_category_id`);
/* 2020-05-16-10 */	ALTER TABLE `events_contacts` ADD UNIQUE `event_id` (`event_id`, `contact_id`, `role_category_id`), ADD INDEX `role_category_id` (`role_category_id`), DROP INDEX `event_id`;
/* 2020-05-16-11 */	INSERT INTO webpages (`title`, `content`, `identifier`, `ending`, `sequence`, `mother_page_id`, `live`, `menu`, `last_update`) VALUES ('iCalendar', '%%% request ics * %%%', '/ics*', 'none', 30, (SELECT page_id FROM webpages wp WHERE identifier = '/'), 'yes', NULL, NOW());
/* 2020-05-16-12 */	INSERT INTO webpages (`title`, `content`, `identifier`, `ending`, `sequence`, `mother_page_id`, `live`, `menu`, `last_update`) VALUES ('Events', '%%% request events %%%', '/events', '/', 20, (SELECT page_id FROM webpages wp WHERE identifier = '/'), 'yes', NULL, NOW());
/* 2020-05-16-13 */	INSERT INTO webpages (`title`, `content`, `identifier`, `ending`, `sequence`, `mother_page_id`, `live`, `menu`, `last_update`) VALUES ('Events', '%%% request events * %%%', '/events*', '/', 1, (SELECT page_id FROM webpages wp WHERE identifier = '/events'), 'yes', NULL, NOW());
/* 2020-05-16-14 */	ALTER TABLE `events` ADD `timezone` varchar(5) NOT NULL AFTER `time_end`;
/* 2020-05-16-15 */	UPDATE `events` SET `timezone` = '+0200';
/* 2020-07-15-1 */	ALTER TABLE `events` CHANGE `timezone` `timezone` varchar(5) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `time_end`;
/* 2020-09-09-1 */	ALTER TABLE `events_contacts` CHANGE `role_category_id` `role_category_id` int unsigned NOT NULL AFTER `contact_id`, CHANGE `sequence` `sequence` tinyint unsigned NOT NULL AFTER `role_category_id`;
/* 2020-10-08-1 */	ALTER TABLE `events` ADD `hashtag` varchar(40) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `registration`;
/* 2022-04-17-1 */	ALTER TABLE `events` ADD `event_year` year NULL AFTER `date_end`;
/* 2022-06-10-1 */	ALTER TABLE `events` CHANGE `identifier` `identifier` varchar(63) COLLATE 'latin1_general_ci' NOT NULL AFTER `event`, CHANGE `date_begin` `date_begin` date NULL AFTER `event_category_id`, CHANGE `timezone` `timezone` varchar(5) COLLATE 'latin1_general_ci' NULL AFTER `time_end`, CHANGE `direct_link` `direct_link` varchar(255) COLLATE 'latin1_general_ci' NULL AFTER `created`, CHANGE `published` `published` enum('yes','no') COLLATE 'latin1_general_ci' NOT NULL DEFAULT 'no' AFTER `direct_link`, CHANGE `takes_place` `takes_place` enum('yes','no') COLLATE 'latin1_general_ci' NOT NULL DEFAULT 'yes' AFTER `published`, CHANGE `show_in_news` `show_in_news` enum('yes','no') COLLATE 'latin1_general_ci' NOT NULL DEFAULT 'no' AFTER `takes_place`, CHANGE `following` `following` enum('yes','no') COLLATE 'latin1_general_ci' NOT NULL DEFAULT 'no' AFTER `show_in_news`;
/* 2022-06-10-2 */	CREATE TABLE `eventtexts` (`eventtext_id` int unsigned NOT NULL AUTO_INCREMENT, `event_id` int unsigned NOT NULL, `eventtext` text COLLATE utf8mb4_unicode_ci NOT NULL, `eventtext_category_id` int unsigned NOT NULL, `last_update` timestamp NOT NULL, PRIMARY KEY (`eventtext_id`), UNIQUE KEY `event_id` (`event_id`,`eventtext_category_id`), KEY `eventtext_category_id` (`eventtext_category_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/* 2022-06-10-3 */	INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'categories ', 'category_id', (SELECT DATABASE()), 'eventtexts', 'eventtext_id', 'eventtext_category_id', 'no-delete');
/* 2022-06-10-4 */	INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'events ', 'event_id', (SELECT DATABASE()), 'eventtexts', 'eventtext_id', 'event_id', 'delete');
/* 2022-06-10-5 */	INSERT INTO categories (`category`, `category_short`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ("Event Texts", NULL, NULL, NULL, "event-texts", "&alias=event-texts", NULL, NOW());
/* 2022-06-10-6 */	ALTER TABLE `events_categories` ADD UNIQUE `event_id_category_id` (`event_id`, `category_id`), DROP INDEX `date_id_category_id`;
/* 2022-06-10-7 */	ALTER TABLE `events_contacts` CHANGE `last_update` `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `sequence`;
/* 2022-06-10-8 */	ALTER TABLE `eventtexts` CHANGE `last_update` `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `eventtext_category_id`;
/* 2022-06-10-9 */	ALTER TABLE `events_categories` ADD `type_category_id` int unsigned NOT NULL AFTER `category_id`;
/* 2022-06-10-10 */	ALTER TABLE `events_categories` ADD INDEX `type_category_id` (`type_category_id`);
/* 2022-06-10-11 */	ALTER TABLE `events_categories` CHANGE `last_update` `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `type_category_id`;
/* 2022-06-10-12 */	INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'categories', 'category_id', (SELECT DATABASE()), 'events_categories', 'event_category_id', 'type_category_id', 'no-delete');
/* 2022-06-10-13 */	UPDATE `events_categories` SET `type_category_id` = (SELECT `category_id` FROM `categories` WHERE `path` = 'events' OR `parameters` LIKE '%&alias=events%');
/* 2022-12-26-1 */	ALTER TABLE `eventtexts` ADD `published` enum('yes','no') COLLATE 'latin1_general_ci' NOT NULL DEFAULT 'yes' AFTER `eventtext_category_id`;
/* 2022-12-26-2 */	ALTER TABLE `eventtexts` ADD INDEX `published` (`published`);
/* 2023-01-08-1 */	CREATE TABLE `eventmenus` (`eventmenu_id` int unsigned NOT NULL AUTO_INCREMENT, `event_id` int unsigned NOT NULL, `menu` varchar(63) COLLATE utf8mb4_unicode_ci NOT NULL, `path` varchar(63) COLLATE utf8mb4_unicode_ci NOT NULL, `sequence` tinyint unsigned NOT NULL, `parameters` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL, `last_update` timestamp NOT NULL, PRIMARY KEY (`eventmenu_id`), UNIQUE KEY `event_id_path` (`event_id`,`path`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/* 2023-01-08-2 */	INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'events ', 'event_id', (SELECT DATABASE()), 'eventmenus', 'eventmenu_id', 'event_id', 'delete');
/* 2023-01-31-1 */	ALTER TABLE `events` ADD `website_id` int unsigned NOT NULL AFTER `main_event_id`;
/* 2023-01-31-2 */	UPDATE `events` SET `website_id` = 1 WHERE `website_id` = 0;
/* 2023-01-31-3 */	ALTER TABLE `events` ADD UNIQUE `identifier_website_id` (`identifier`, `website_id`), DROP INDEX `identifier`;
/* 2023-01-31-4 */	INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'websites', 'website_id', (SELECT DATABASE()), 'events', 'event_id', 'website_id', 'no-delete');
/* 2023-04-03-1 */	ALTER TABLE `events` CHANGE `website_id` `website_id` int unsigned NOT NULL DEFAULT '1' AFTER `main_event_id`;
/* 2023-06-07-1 */	ALTER TABLE `events` ADD `parameters` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `main_event_id`;
/* 2023-06-12-1 */	ALTER TABLE `events_categories` ADD `property` varchar(255) NULL AFTER `category_id`;
