<?php

/**
 * events module
 * copy a timetable of an event to another event
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2015-2016, 2019-2020, 2022-2026 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_events_make_timetablecopy($params, $settings, $event) {
	$sql = 'SELECT DATEDIFF(date_end, date_begin) AS days
		FROM events
		WHERE event_id = %d';
	$sql = sprintf($sql, $event['event_id']);
	$event['days'] = wrap_db_fetch($sql, '', 'single value');

	$page['dont_show_h1'] = true;
	$page['query_strings'][] = 'event';
	$page['title'] = wrap_text('Copy timetable: %s %s', [
		'values' => [$event['event'], $event['year']]
	]);
	$page['breadcrumbs'][]['title'] = wrap_text('Copy');

	if (!empty($_GET['event'])) {
		$event['source_identifier'] = $_GET['event'];
		$event = mod_events_make_timetablecopy_read($event);
	}
	if (!empty($_POST['write']))
		$event = mod_events_make_timetablecopy_action($event);

	$page['text'] = wrap_template('timetablecopy', $event);
	return $page;
}

function mod_events_make_timetablecopy_read($event) {
	// does an event exist?
	$sql = 'SELECT event_id, date_begin, date_end
			, DATEDIFF(date_end, date_begin) AS days
		FROM events
		WHERE identifier = "%s"
		AND events.event_category_id = /*_ID categories event/event _*/';
	$sql = sprintf($sql, wrap_db_escape($event['source_identifier']));
	$event['source'] = wrap_db_fetch($sql);
	if (!$event['source']) return $event;
	
	// Are events the same length?
	if ($event['days'] !== $event['source']['days']) {
		$event['days_not_equal'] = true;
		$event['source'] = [];
		return $event;
	}
	
	$sql = 'SELECT event_id, event, event_category_id
			, events.description, date_begin, date_end
			, TIME_FORMAT(time_begin, "%%H:%%i") AS time_begin
			, TIME_FORMAT(time_end, "%%H:%%i") AS time_end
			%s
		FROM events
		LEFT JOIN categories
			ON events.event_category_id = categories.category_id
		WHERE main_event_id = %d
		AND categories.parameters LIKE "%%&events_timetable_copy=1%%"
		ORDER BY IFNULL(date_begin, date_end), IFNULL(time_begin, time_end)';
	$sql = sprintf($sql
		, wrap_setting('events_timetable_extra_fields') ? ','.implode(',', wrap_setting('events_timetable_extra_fields')) : ''
		, $event['source']['event_id']
	);
	$event['destination'] = wrap_db_fetch($sql, 'event_id');
	if (!$event['destination']) return $event;

	if ($event['date_begin'] !== $event['source']['date_begin']) {
		$destination_days = mod_events_make_timetablecopy_dates($event['date_begin'], $event['date_end']);
		$source_days = mod_events_make_timetablecopy_dates($event['source']['date_begin'], $event['source']['date_end']);
		foreach ($source_days as $index => $day)
			$convert[$day] = $destination_days[$index];
		foreach ($event['destination'] as $id => $timetable)
			$event['destination'][$id]['date_begin'] = $convert[$timetable['date_begin']];
	}
	return $event;
}

function mod_events_make_timetablecopy_dates($start, $end) {
	$dates = [];
	$current = new DateTime($start);
	$current->modify('-1 day');
	$end = new DateTime($end ?? $start);
	$end->modify('+1 day');

	while ($current <= $end) {
		$dates[] = $current->format('Y-m-d');
		$current->modify('+1 day');
	}
	return $dates;
}

function mod_events_make_timetablecopy_action($event) {
	foreach ($event['destination'] as $line) {
		unset($line['event_id']);
		$line['main_event_id'] = $event['event_id'];
		zzform_insert('events', $line);
	}
	return wrap_redirect_change(wrap_path('events_internal_event', $event['identifier']));
}
