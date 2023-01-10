<?php 

/**
 * events module
 * Table definition for 'eventtexts'
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2022-2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Event Texts';
$zz['table'] = '/*_PREFIX_*/eventtexts';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'eventtext_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'event_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = sprintf('SELECT event_id
		, CONCAT(/*_PREFIX_*/events.event, " (", DATE_FORMAT(/*_PREFIX_*/events.date_begin, "%s"), ")") AS event
		, identifier
	FROM /*_PREFIX_*/events
	WHERE ISNULL(main_event_id)
	ORDER BY date_begin DESC', wrap_placeholder('mysql_date_format'));
$zz['fields'][2]['display_field'] = 'identifier';
$zz['fields'][2]['search'] = sprintf('CONCAT(/*_PREFIX_*/events.event, " (", 
	DATE_FORMAT(/*_PREFIX_*/events.date_begin, "%s"), ")")', wrap_placeholder('mysql_date_format'));
$zz['fields'][2]['if']['where']['hide_in_list'] = true;
$zz['fields'][2]['if']['where']['hide_in_form'] = true;

$zz['fields'][4]['title'] = 'Category';
$zz['fields'][4]['field_name'] = 'eventtext_category_id';
$zz['fields'][4]['type'] = 'select';
$zz['fields'][4]['sql'] = 'SELECT category_id, category, description, main_category_id
	FROM categories
	ORDER BY sequence';
$zz['fields'][4]['show_hierarchy'] = 'main_category_id';
$zz['fields'][4]['id_field_name'] = 'category_id';
$zz['fields'][4]['show_hierarchy_subtree'] = wrap_category_id('event-texts');
$zz['fields'][4]['display_field'] = 'category';

$zz['fields'][3]['title'] = 'Text';
$zz['fields'][3]['field_name'] = 'eventtext';
$zz['fields'][3]['type'] = 'memo';
$zz['fields'][3]['format'] = 'markdown';
$zz['fields'][3]['list_format'] = 'markdown';
$zz['fields'][3]['if'][1]['list_prefix'] = '<del>';
$zz['fields'][3]['if'][1]['list_suffix'] = '</del>';

$zz['fields'][5]['field_name'] = 'published';
$zz['fields'][5]['type'] = 'select';
$zz['fields'][5]['enum'] = ['yes', 'no'];
$zz['fields'][5]['default'] = 'yes';
$zz['fields'][5]['hide_in_list'] = true;

$zz['fields'][99]['field_name'] = 'last_update';
$zz['fields'][99]['type'] = 'timestamp';
$zz['fields'][99]['hide_in_list'] = true;

$zz['sql'] = 'SELECT /*_PREFIX_*/eventtexts.*
		, /*_PREFIX_*/events.identifier
		, /*_PREFIX_*/categories.category
	FROM /*_PREFIX_*/eventtexts
	LEFT JOIN /*_PREFIX_*/events USING (event_id)
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/eventtexts.eventtext_category_id = /*_PREFIX_*/categories.category_id
';

$zz['sqlorder'] = ' ORDER BY date_begin DESC, IFNULL(time_begin, time_end) DESC, sequence DESC, identifier DESC';

$zz['conditions'][1]['scope'] = 'record';
$zz['conditions'][1]['where'] = '/*_PREFIX_*/eventtexts.published = "no"';
