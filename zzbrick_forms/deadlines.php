<?php 

/**
 * events module
 * form for deadlines
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2014-2021, 2023-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz = zzform_include('events');

$sql = 'SELECT category_id, category
    FROM /*_PREFIX_*/categories
    WHERE parameters LIKE "%events_deadlines=1%"';
$deadline_categories = wrap_db_fetch($sql, 'category_id');
if (!$deadline_categories) wrap_quit(404, wrap_text(
	'No categories defined for deadlines (with parameter `%s`)',
	['values' => ['events_deadlines']]
));
$deadline_categories = wrap_translate($deadline_categories, 'categories', 'category_id');

$zz['title'] = 'Deadlines';
$zz['explanation'] = wrap_template('event-deadlines');
$zz['where']['main_event_id'] = $brick['data']['event_id'];

foreach ($zz['fields'] as $no => $field) {
	$identifier = zzform_field_identifier($field);
	switch ($identifier) {
	case 'event_id':
	case 'date_begin':
	case 'date_end':
	case 'time_begin':
	case 'time_end':
	case 'event':
	case 'main_event_id':
	case 'published':
	case 'last_update':
		break;

	case 'identifier':
		$zz['fields'][$no]['fields'] = [
			'main_event_id[identifier]', 'event', 'identifier'
		];
		break;
	
	case 'event_category_id':
		$zz['fields'][$no]['type'] = 'write_once';
		break;

	case 'website_id':
		$zz['fields'][$no]['type'] = 'hidden';
		$zz['fields'][$no]['hide_in_form'] = true;
		$zz['fields'][$no]['value'] = $brick['data']['website_id'];
		break;

	case 'takes_place':
		$zz['fields'][$no]['hide_in_form'] = true;
		break;
	
	default:
		unset($zz['fields'][$no]);
		break;
	}
}

$zz['sql'] = wrap_edit_sql($zz['sql'], 'WHERE',
	sprintf('events.event_category_id IN (%s)', implode(',', array_keys($deadline_categories)))
);

$zz['page']['referer'] = '../';

foreach ($deadline_categories as $category) {
	$zz['add'][] = [
		'type' => $category['category'],
		'field_name' => 'event_category_id',
		'value' => $category['category_id']
	];
}
