/**
 * events module
 * SQL updates 
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020 Gustaf Mossakowski
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
