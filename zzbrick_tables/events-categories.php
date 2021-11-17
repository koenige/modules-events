<?php 

/**
 * events module
 * Table definition for 'events/categories'
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
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
$zz['fields'][2]['sql'] = sprintf('SELECT event_id
	, CONCAT(/*_PREFIX_*/events.event
		, " (", DATE_FORMAT(/*_PREFIX_*/events.date_begin, "%s"), ")") AS event 
	FROM /*_PREFIX_*/events
	WHERE ISNULL(main_event_id)
	ORDER BY date_begin DESC'
	, wrap_placeholder('mysql_date_format'));
$zz['fields'][2]['display_field'] = 'event';
$zz['fields'][2]['search'] = sprintf('CONCAT(/*_PREFIX_*/events.event, " (", 
	DATE_FORMAT(/*_PREFIX_*/events.date_begin, "%s"), ")")'
	, wrap_placeholder('mysql_date_format'));

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

$zz['sql'] = sprintf('SELECT /*_PREFIX_*/events_categories.*
		, CONCAT(/*_PREFIX_*/events.event, " (", DATE_FORMAT(events.date_begin, "%s"), IFNULL(CONCAT("–", DATE_FORMAT(events.date_end, "%s")), ""), ")") AS event
		, category
	FROM /*_PREFIX_*/events_categories
	LEFT JOIN /*_PREFIX_*/categories USING (category_id)
	LEFT JOIN /*_PREFIX_*/events
		ON /*_PREFIX_*/events_categories.event_id = /*_PREFIX_*/events.event_id
'
	, wrap_placeholder('mysql_date_format')
	, wrap_placeholder('mysql_date_format')
);
$zz['sqlorder'] = ' ORDER BY date_begin, category';
