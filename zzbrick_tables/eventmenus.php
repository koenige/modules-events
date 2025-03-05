<?php 

/**
 * events module
 * Table definition for 'eventmenus'
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2022-2023, 2025 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Event Menus';
$zz['table'] = '/*_PREFIX_*/eventmenus';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'eventmenu_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][5]['title_tab'] = 'Seq.';
$zz['fields'][5]['field_name'] = 'sequence';
$zz['fields'][5]['type'] = 'number';

$zz['fields'][2]['field_name'] = 'event_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = sprintf('SELECT event_id
		, CONCAT(/*_PREFIX_*/events.event, " (", DATE_FORMAT(/*_PREFIX_*/events.date_begin, "%s"), ")") AS event
		, identifier
	FROM /*_PREFIX_*/events
	WHERE /*_PREFIX_*/events.event_category_id = /*_ID categories event/event _*/
	ORDER BY date_begin DESC', wrap_placeholder('mysql_date_format'));
$zz['fields'][2]['display_field'] = 'event';
$zz['fields'][2]['search'] = sprintf('CONCAT(/*_PREFIX_*/events.event, " (", 
	DATE_FORMAT(/*_PREFIX_*/events.date_begin, "%s"), ")")', wrap_placeholder('mysql_date_format'));
$zz['fields'][2]['if']['where']['hide_in_form'] = true;
$zz['fields'][2]['if']['where']['hide_in_list'] = true;

$zz['fields'][3]['field_name'] = 'menu';

$zz['fields'][4]['field_name'] = 'path';

$zz['fields'][6]['field_name'] = 'parameters';
$zz['fields'][6]['type'] = 'parameter';

$zz['fields'][99]['field_name'] = 'last_update';
$zz['fields'][99]['type'] = 'timestamp';
$zz['fields'][99]['hide_in_list'] = true;

$zz['sql'] = 'SELECT /*_PREFIX_*/eventmenus.*
		, CONCAT(event, " ", events.date_begin, " - ", events.date_end) AS event
	FROM /*_PREFIX_*/eventmenus
	LEFT JOIN /*_PREFIX_*/events USING (event_id)
';

$zz['sqlorder'] = ' ORDER BY identifier DESC, /*_PREFIX_*/eventmenus.sequence';
