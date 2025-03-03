<?php 

/**
 * events module
 * placeholder function for project
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_events_placeholder_project($brick) {
	global $zz_page;

	$sql = 'SELECT event_id, event, event_abbr, identifier
			, SUBSTRING_INDEX(identifier, "/", -1) AS event_idf
			, CONCAT("event_id:", events.event_id) AS event_rights
			, CONCAT("main_event_id:", events.main_event_id) AS main_event_rights
			, events.parameters
	    FROM events
	    WHERE identifier = "%s"';
	$sql = sprintf($sql, wrap_db_escape($brick['parameter']));
	$event = wrap_db_fetch($sql);
	if (!$event AND empty($brick['local_settings']['not_found']))
		wrap_quit(404);
	elseif (!$event)
		return $brick;
	$event = wrap_translate($event, 'events');

//	if ($event['parameters'])
//		wrap_match_module_parameters('events', $event['parameters']);

//	$zz_page['access'][] = $event['event_rights'];
//	if ($event['main_event_rights'])
//		$zz_page['access'][] = $event['main_event_rights'];
//	wrap_access_page($zz_page['db']['parameters'] ?? '', $zz_page['access']);

	// breadcrumbs
	$zz_page['breadcrumb_placeholder'][] = [
		'title' => $event['event_abbr'] ?? $event['event'],
		'url_path' => $event['event_idf']
	];
	$brick['data'] = $event;
	return $brick;
}
