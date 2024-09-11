<?php 

/**
 * events module
 * form for timetable
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2014-2015, 2018, 2020-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


if (empty($brick['data']['event_id'])) wrap_quit(404);

$zz = zzform_include('events');

$zz['title'] = 'Timetable';
$zz['where']['main_event_id'] = $brick['data']['event_id'];
$zz['list']['group'] = 'date_begin';

foreach ($zz['fields'] as $no => $field) {
	$identifier = zzform_field_identifier($field);
	switch ($identifier) {
	case 'event_id':
	case 'time_end':
	case 'timezone':
	case 'takes_place':
	case 'description':
	case 'last_update':
		break;

	case 'identifier':
		$zz['fields'][$no]['fields'] = ['main_event_id[identifier]'];
		$zz['fields'][$no]['identifier']['exists'] = '/';
		$zz['fields'][$no]['identifier']['slashes'] = true;
		break;

	case 'date_begin':
		$zz['fields'][$no]['default'] = $brick['data']['timetable_max'] ?? '';
		$zz['fields'][$no]['list_append_next'] = false;
		break;

	case 'date_end':
		$zz['fields'][$no]['hide_in_list'] = true;
		$zz['fields'][$no]['list_append_next'] = false;
		break;

	case 'time_begin':
		$zz['fields'][$no]['title_tab'] = 'Time';
		$zz['fields'][$no]['list_prefix'] = '';
		break;
	
	case 'event':
		unset($zz['fields'][$no]['link']);
		break;
	
	case 'places':
		$zz['fields'][$no]['list_append_next'] = false;
		break;

	case 'event_category_id':
		$zz['fields'][$no]['value'] = wrap_category_id('event/item');
		break;

	case 'published':
		$zz['fields'][$no]['hide_in_list'] = true; // @todo DEL/DEL
		break;

	case 'following':
		$zz['fields'][$no]['hide_in_form'] = false;
		break;

	case 'sequence':
		$zz['fields'][$no]['hide_in_form'] = false;
		$zz['fields'][$no]['hide_in_list'] = false;
		$zz['fields'][$no]['type'] = 'sequence';
		break;

	case 'events_media':
		$zz['fields'][$no]['max_records'] = 1; // max. 1 image per item
		break;

	case 'main_event_id':
		$zz['fields'][$no]['hide_in_form'] = true;
		$zz['fields'][$no]['value'] = $brick['data']['event_id'];
		$zz['fields'][$no]['type_detail'] = 'select';
		break;

	case 'created':
		$zz['fields'][$no]['hide_in_form'] = true;
		break;

	default:
		unset($zz['fields'][$no]);
		break;
	}
}

$zz['sql'] = wrap_edit_sql($zz['sql'], 'WHERE', 'events.event_category_id = /*_ID categories event/item _*/');
$zz['sqlorder'] = 'ORDER BY sequence, date_begin, time_begin';

$zz['page']['referer'] = '../';
$zz['record']['copy'] = true;

unset($zz['filter']);
