<?php 

/**
 * events module
 * Table definition for 'events/categories'
 *
 * Part of »Zugzwang Project«
 * http://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2014, 2018, 2020 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Events/Categories';
$zz['table'] = '/*_PREFIX_*/events_categories';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'event_category_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'event_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT event_id
	, CONCAT(/*_PREFIX_*/events.event, " (", DATE_FORMAT(/*_PREFIX_*/events.date_begin, "%Y-%m-%d")
		, IFNULL(CONCAT(", ", contact), ""), ")") AS event 
	FROM /*_PREFIX_*/events
	LEFT JOIN /*_PREFIX_*/contacts
		ON /*_PREFIX_*/events.place_contact_id = /*_PREFIX_*/contacts.contact_id
	WHERE ISNULL(main_event_id)
	ORDER BY date_begin DESC';
$zz['fields'][2]['display_field'] = 'event';
$zz['fields'][2]['search'] = 'CONCAT(/*_PREFIX_*/events.event, " (", 
	DATE_FORMAT(/*_PREFIX_*/events.date_begin, "%Y-%m-%d"), IFNULL(CONCAT(", ", contact), ""), ")")';

$zz['fields'][3]['field_name'] = 'category_id';
$zz['fields'][3]['type'] = 'select';
$zz['fields'][3]['sql'] = 'SELECT category_id, category, description, main_category_id
	FROM /*_PREFIX_*/categories
	ORDER BY sequence, category';
$zz['fields'][3]['display_field'] = 'category';
$zz['fields'][3]['search'] = '/*_PREFIX_*/categories.category';
$zz['fields'][3]['show_hierarchy'] = 'main_category_id';
$zz['fields'][3]['show_hierarchy_subtree'] = wrap_category_id('events');

$zz['fields'][99]['field_name'] = 'last_update';
$zz['fields'][99]['type'] = 'timestamp';
$zz['fields'][99]['hide_in_list'] = true;

$zz['subselect']['sql'] = 'SELECT event_id, category
	FROM /*_PREFIX_*/events_categories
	LEFT JOIN /*_PREFIX_*/categories USING (category_id)
';
$zz['subselect']['concat_rows'] = ', ';

$zz['sql'] = 'SELECT /*_PREFIX_*/events_categories.*
		, CONCAT(events.date_begin, IFNULL(CONCAT(" - ", events.date_end), "")) AS event
		, category
	FROM /*_PREFIX_*/events_categories
	LEFT JOIN /*_PREFIX_*/categories USING (category_id)
	LEFT JOIN /*_PREFIX_*/events
		ON /*_PREFIX_*/events_categories.event_id = /*_PREFIX_*/events.event_id
';
$zz['sqlorder'] = ' ORDER BY date_begin, category';
