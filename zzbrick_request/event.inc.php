<?php 

/**
 * events module
 * Table definition for 'events'
 *
 * Part of »Zugzwang Project«
 * http://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2014-2020 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_events_event($params) {
	global $zz_setting;

	if (count($params) !== 2) return false;

	if ($zz_setting['local_access'] OR !empty($_SESSION['logged_in'])) {
		$published = '(events.published = "yes" OR events.published = "no")';
		$zz_setting['cache'] = false;
	} else {
		$published = 'events.published = "yes"';
	}
	
	$sql = 'SELECT event_id
	    FROM events
	    WHERE identifier = "%d/%s"
	    AND event_category_id = %d
	    AND %s';
	$sql = sprintf($sql
		, $params[0], wrap_db_escape($params[1])
		, wrap_category_id('event/event')
		, $published
	);
	$event = wrap_db_fetch($sql);
	
	if (count($event) !== 1) {
		$event['not_found'] = true;
		$page['text'] = wrap_template('event', $event);
		$page['status'] = 404;
		return $page;
	}

	require_once __DIR__.'/../zzbrick_request_get/event.inc.php';
	$event = mod_events_get_event($event['event_id']);
	
	$lightbox = false;
	$event['timetable'] = mod_events_event_timetable($event['event_id']);
	if ($event['timetable']) {
		foreach ($event['timetable'] as $day => $timetable) {
			foreach ($timetable['hours'] as $timetable_event_id => $single_event) {
				if ($single_event['event_category_id'] === wrap_category_id('event/event')) {
					$event['timetable']['programme'] = true;
				}
				if (empty($single_event['images'])) continue;
				$lightbox = true;
			}
		}
		$event['timetable'] = wrap_template('timetable', $event['timetable']);
	} else {
		$event['timetable'] = '';
	}

	if (strstr($event['description'], '%%% timetable %%%')) {
		$event['description'] = str_replace('%%% timetable %%%', $event['timetable'], $event['description']);
		unset ($event['timetable']);
	}

	foreach ($event as $field => $values) {
		if (!is_array($values)) continue;
		foreach ($values as $index => $value) {
			if (empty($value['contact_path'])) continue;
			$event[$field][$index][$value['contact_path']] = true;	
		}
	}

	if (!empty($event['links'])) {
		$event['links'] = wrap_template('filelinks', $event['links']);
	}
	if (!empty($event['images'])) {
		$lightbox = true;
	}
	brick_request_links($event['description'], $event, 'sequence');
	$page['head'] = '';
	if ($lightbox) {
		$page['extra']['magnific_popup'] = true;
	}
	
	if (!empty($event['cancelled'])) {
		$page['status'] = 404;
	}
	$page['text'] = wrap_template('event', $event);
// @todo check for latitude, longitude in $event['location']
//	if ($event['places']) {
//		$page['head'] .= wrap_template('leaflet-head');
//	}
	$page['meta'] = [
		0 => ['property' => 'og:url', 'content' => $zz_setting['host_base'].$zz_setting['request_uri']],
		1 => ['property' => 'og:type', 'content' => 'article'],
		2 => ['property' => 'og:title', 'content' => wrap_html_escape(strip_tags($event['event']))],
		3 => ['property' => 'og:description', 'content' => wrap_html_escape(trim(strip_tags(markdown($event['abstract']))))]
	];
	if (!empty($event['images'])) {
		$main_img = reset($event['images']);
		$page['meta'][] 
			= ['property' => 'og:image', 'content' => $zz_setting['host_base'].$zz_setting['files_path'].'/'.$main_img['filename'].'.'.wrap_get_setting('news_og_image_size').'.'.$main_img['thumb_extension'].'?v='.$main_img['version']];
	}
	$page['title'] = $event['event'].', '.wrap_date($event['duration']);
	$page['breadcrumbs'][] = '<a href="'.$zz_setting['events_path'].'/'.$event['year'].'/">'.$event['year'].'</a>';
	$page['breadcrumbs'][] = $event['event'];
	$page['dont_show_h1'] = true;
	return $page;
}

/**
 * get a timetable for an event
 *
 * @param int $event_id
 * @return array
 */
function mod_events_event_timetable($event_id) {
	global $zz_setting;
	if ($zz_setting['local_access'] OR !empty($_SESSION['logged_in']))
		$published = '(published = "yes" OR published = "no")';
	else
		$published = 'published = "yes"';

	$sql = 'SELECT event_id, event, description, date_begin, date_end
			, CONCAT(date_begin, IFNULL(CONCAT("/", date_end), "")) AS duration
			, TIME_FORMAT(time_begin, "%%H.%%i") AS time_begin
			, time_begin AS time_begin_iso
			, TIME_FORMAT(time_end, "%%H.%%i") AS time_end
			, time_end AS time_end_iso
			, IF(following = "yes", 1, NULL) AS following
			, IF(takes_place = "yes", NULL, 1) AS cancelled
			, CONCAT(CASE DAYOFWEEK(date_begin) WHEN 1 THEN "%s"
				WHEN 2 THEN "%s"
				WHEN 3 THEN "%s"
				WHEN 4 THEN "%s"
				WHEN 5 THEN "%s"
				WHEN 6 THEN "%s"
				WHEN 7 THEN "%s" END) AS weekday
			, event_category_id
			, IF(event_category_id = %d, identifier, NULL) AS identifier
		FROM events
		WHERE %s
		AND main_event_id = %d
		ORDER BY sequence, date_begin, time_begin, time_end, identifier';
	$sql = sprintf($sql
		, wrap_text('Sun'), wrap_text('Mon'), wrap_text('Tue'), wrap_text('Wed') 
		, wrap_text('Thu'), wrap_text('Fri'), wrap_text('Sat')
		, wrap_category_id('event/event')
		, $published
		, $event_id
	);
	$events = wrap_db_fetch($sql, ['date_begin', 'event_id'], 'list date_begin hours');
	$events_db = wrap_db_fetch($sql, 'event_id');
	$events_db = wrap_translate($events_db, 'events');
	if (!$events_db) return [];

	// get media, set weekday
	$events = [];
	foreach ($events_db as $event_id => $event) {
		$day = $event['date_begin'];
		$events[$day]['date_begin'] = $day;
		$events[$day]['weekday'] = $event['weekday'];
		$events[$day]['hours'][] = $event;
	}
	$events_media = wrap_get_media(array_keys($events_db), 'events', 'event');

	// get categories
	$categories = mod_events_get_event_categories(array_keys($events_db));
	
	// save media, categories
	foreach ($events as $day => $timetable) {
		foreach ($timetable['hours'] as $timetable_event_id => $single_event) {
			if (array_key_exists($timetable_event_id, $categories)) {
				$events[$day]['hours'][$timetable_event_id]['categories']
					= $categories[$timetable_event_id];
			}
			if (!array_key_exists($timetable_event_id, $events_media)) continue;
			if (empty($events_media[$timetable_event_id]['images'])) continue;
			$events[$day]['hours'][$timetable_event_id]['images']
					= $events_media[$timetable_event_id]['images'];
		}
	}

	$events = array_values($events);
	$events['images'] = [];
	foreach ($events_media as $event_id => $event_media) {
		if (empty($event_media['images'])) continue;
		$events['images'] += $event_media['images'];
	}
	return $events;
}
