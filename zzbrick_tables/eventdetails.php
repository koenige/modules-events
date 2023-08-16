<?php 

/**
 * events module
 * Table definition for 'eventdetails'
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2012, 2014, 2019-2020, 2022-2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */



$zz['title'] = 'Event Details';
$zz['table'] = 'eventdetails';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'eventdetail_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'event_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT event_id, event
		, CONCAT(IFNULL(events.date_begin, ""), IFNULL(CONCAT("/", events.date_end), "")) AS duration
		, identifier
	FROM /*_PREFIX_*/events
	WHERE ISNULL(main_event_id)
	ORDER BY identifier DESC';
$zz['fields'][2]['sql_format'][2] = 'wrap_date';
$zz['fields'][2]['display_field'] = 'event';

$zz['fields'][3]['field_name'] = 'identification';
$zz['fields'][3]['type'] = 'url';
$zz['fields'][3]['list_append_next'] = true;
$zz['fields'][3]['placeholder'] = 'URL';

$zz['fields'][4]['field_name'] = 'label';
$zz['fields'][4]['type'] = 'text';
$zz['fields'][4]['list_prefix'] = '<br>';
$zz['fields'][4]['placeholder'] = true;

$zz['fields'][5]['title'] = 'Type';
$zz['fields'][5]['field_name'] = 'detail_category_id';
$zz['fields'][5]['type'] = 'select';
$zz['fields'][5]['sql'] = sprintf('SELECT category_id, category
	FROM /*_PREFIX_*/categories
	WHERE main_category_id = %d',
	wrap_category_id('event-details')
);
$zz['fields'][5]['display_field'] = 'category';
$zz['fields'][5]['character_set'] = 'utf8';

$zz['fields'][6]['field_name'] = 'active';
$zz['fields'][6]['type'] = 'select';
$zz['fields'][6]['enum'] = ['yes', 'no'];
$zz['fields'][6]['default'] = 'yes';
$zz['fields'][6]['for_action_ignore'] = true;

$zz['fields'][99]['field_name'] = 'last_update';
$zz['fields'][99]['type'] = 'timestamp';
$zz['fields'][99]['hide_in_list'] = true;


$zz['sql'] = sprintf('SELECT /*_PREFIX_*/eventdetails.*
		, CONCAT(event, " (", IFNULL(DATE_FORMAT(date_begin, "%s"), ""), IFNULL(CONCAT("–", DATE_FORMAT(date_end, "%s")), ""), ")") AS event
		, /*_PREFIX_*/categories.category
	FROM /*_PREFIX_*/eventdetails
	LEFT JOIN /*_PREFIX_*/events USING (event_id)
	LEFT JOIN /*_PREFIX_*/categories
		ON eventdetails.detail_category_id = categories.category_id
'
	, wrap_placeholder('mysql_date_format')
	, wrap_placeholder('mysql_date_format')
);
$zz['sqlorder'] = ' ORDER BY IFNULL(date_begin, date_end) DESC, time_begin DESC,
	/*_PREFIX_*/events.identifier, /*_PREFIX_*/eventdetails.identification';

$zz['filter'][1]['title'] = wrap_text('Active');
$zz['filter'][1]['identifier'] = 'active';
$zz['filter'][1]['type'] = 'list';
$zz['filter'][1]['where'] = '/*_PREFIX_*/eventdetails.active';
$zz['filter'][1]['selection']['yes'] = wrap_text('yes');
$zz['filter'][1]['selection']['no'] = wrap_text('no');

$zz['filter'][2]['sql'] = 'SELECT DISTINCT category_id
		, category
	FROM /*_PREFIX_*/eventdetails
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/eventdetails.detail_category_id = /*_PREFIX_*/categories.category_id
	ORDER BY category';
$zz['filter'][2]['title'] = wrap_text('Category');
$zz['filter'][2]['identifier'] = 'category';
$zz['filter'][2]['type'] = 'list';
$zz['filter'][2]['where'] = '/*_PREFIX_*/eventdetails.detail_category_id';
