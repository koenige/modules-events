<?php 

/**
 * events module
 * placeholder function for event
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_events_placeholder_event($brick) {
	global $zz_page;

	$sql = 'SELECT event_id, event, identifier
			, IFNULL(event_year, YEAR(IFNULL(date_begin, date_end))) AS year
			, SUBSTRING_INDEX(identifier, "/", -1) AS event_idf
			, CONCAT("event_id:", events.event_id) AS event_rights
			, CONCAT("main_event_id:", events.main_event_id) AS main_event_rights
			, CONCAT(IFNULL(date_begin, ""), IFNULL(CONCAT("/", date_end), "")) AS duration
			, IF(events.published = "yes", 1, NULL) AS published
			, IFNULL((SELECT MAX(timetable.date_begin) FROM events timetable
				WHERE timetable.main_event_id = events.event_id), date_begin) AS timetable_max
	    FROM events
	    WHERE identifier = "%s"';
	if (in_array('activities', wrap_setting('modules'))) {
		$sql = wrap_edit_sql($sql, 'SELECT', 'forms.form_id');
		$sql = wrap_edit_sql($sql, 'JOIN', 'LEFT JOIN forms USING (event_id)');
	}
	$sql = sprintf($sql, wrap_db_escape($brick['parameter']));
	$event = wrap_db_fetch($sql);
	if (!$event AND empty($brick['local_settings']['not_found']))
		wrap_quit(404);
	elseif (!$event)
		return $brick;
	$brick['data'] = $event;

	// @todo add field events.parameters
//	if ($event['parameters'])
//		wrap_module_parameters('events', $event['parameters']);

	$zz_page['access'][] = $event['event_rights'];
	if ($event['main_event_rights'])
		$zz_page['access'][] = $event['main_event_rights'];
	wrap_access_page($zz_page['db']['parameters'] ?? '', $zz_page['access']);

	// breadcrumbs
	$zz_page['breadcrumb_placeholder'][] = [
		'title' => $brick['data']['year'],
		'url_path' => $brick['data']['year'],
		'add_next' => 1 // add event placeholder as separate breadcrumb
	];
	$zz_page['breadcrumb_placeholder'][] = [
		'title' => $brick['data']['event'],
		'url_path' => $brick['data']['event_idf']
	];
	return $brick;
}
