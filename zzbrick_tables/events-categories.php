<?php 

/**
 * events module
 * Table definition for 'events/categories'
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2014, 2018, 2020-2025 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Categories of Events';
$zz['table'] = '/*_PREFIX_*/events_categories';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'event_category_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'event_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT event_id, event
		, CONCAT(IFNULL(events.date_begin, ""), IFNULL(CONCAT("/", events.date_end), "")) AS duration
		, identifier
	FROM /*_PREFIX_*/events
	WHERE /*_PREFIX_*/events.event_category_id = /*_ID categories event/event _*/
	ORDER BY identifier DESC';
$zz['fields'][2]['sql_format'][2] = 'wrap_date';
$zz['fields'][2]['display_field'] = 'event';
$zz['fields'][2]['search'] = sprintf('CONCAT(/*_PREFIX_*/events.event, " (", 
	DATE_FORMAT(IFNULL(/*_PREFIX_*/events.date_begin, /*_PREFIX_*/events.date_end), "%s"), ")")', wrap_placeholder('mysql_date_format'));

$zz['fields'][4]['title'] = 'No.';
$zz['fields'][4]['field_name'] = 'sequence';
$zz['fields'][4]['type'] = 'number';
$zz['fields'][4]['auto_value'] = 'increment';
$zz['fields'][4]['def_val_ignore'] = true;
$zz['fields'][4]['exclude_from_search'] = true;

$zz['fields'][3]['field_name'] = 'category_id';
$zz['fields'][3]['type'] = 'select';
$zz['fields'][3]['sql'] = 'SELECT category_id, category, main_category_id
	FROM /*_PREFIX_*/categories
	ORDER BY sequence, category';
$zz['fields'][3]['display_field'] = 'category';
$zz['fields'][3]['search'] = '/*_PREFIX_*/categories.category';
$zz['fields'][3]['show_hierarchy'] = 'main_category_id';
$zz['fields'][3]['show_hierarchy_subtree'] = wrap_category_id('events');

$zz['fields'][5]['field_name'] = 'type_category_id';
$zz['fields'][5]['type'] = 'hidden';
$zz['fields'][5]['type_detail'] = 'select';
$zz['fields'][5]['value'] = wrap_category_id('events');
$zz['fields'][5]['hide_in_form'] = true;
$zz['fields'][5]['hide_in_list'] = true;
$zz['fields'][5]['exclude_from_search'] = true;
$zz['fields'][5]['for_action_ignore'] = true;

if (wrap_setting('events_category_properties')) {
	$zz['fields'][6]['field_name'] = 'property';
	$zz['fields'][6]['typo_cleanup'] = true;
}

$zz['fields'][99]['field_name'] = 'last_update';
$zz['fields'][99]['type'] = 'timestamp';
$zz['fields'][99]['hide_in_list'] = true;

$zz['subselect']['sql'] = 'SELECT event_id, category
	FROM /*_PREFIX_*/events_categories
	LEFT JOIN /*_PREFIX_*/categories USING (category_id)
';
$zz['subselect']['concat_rows'] = ', ';

$zz['sql'] = sprintf('SELECT /*_PREFIX_*/events_categories.*
		, CONCAT(event, " (", IFNULL(DATE_FORMAT(date_begin, "%s"), ""), IFNULL(CONCAT("–", DATE_FORMAT(date_end, "%s")), ""), ")") AS event
		, /*_PREFIX_*/categories.category
	FROM /*_PREFIX_*/events_categories
	LEFT JOIN /*_PREFIX_*/categories USING (category_id)
	LEFT JOIN /*_PREFIX_*/events USING (event_id)
	LEFT JOIN /*_PREFIX_*/categories main_categories
		ON /*_PREFIX_*/categories.main_category_id = main_categories.category_id
'
	, wrap_placeholder('mysql_date_format')
	, wrap_placeholder('mysql_date_format')
);
$zz['sqlorder'] = ' ORDER BY IFNULL(date_begin, date_end) DESC, time_begin DESC, events.identifier, sequence, /*_PREFIX_*/categories.category';
