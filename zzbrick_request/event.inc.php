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
	
	$sql = 'SELECT event_id, identifier
			, event, abstract, events.description, date_begin, date_end
			, IF(date_begin >= CURDATE(), registration, NULL) AS registration
			, direct_link
			, CONCAT(date_begin, IFNULL(CONCAT("/", date_end), "")) AS duration
			, TIME_FORMAT(time_begin, "%%H.%%i") AS time_begin
			, time_begin AS time_begin_iso
			, TIME_FORMAT(time_end, "%%H.%%i") AS time_end
			, time_end AS time_end_iso
			, IF(takes_place = "yes", NULL, 1) AS cancelled
			, YEAR(date_begin) AS year
			, CONCAT(CASE DAYOFWEEK(date_begin) WHEN 1 THEN "%s"
				WHEN 2 THEN "%s"
				WHEN 3 THEN "%s"
				WHEN 4 THEN "%s"
				WHEN 5 THEN "%s"
				WHEN 6 THEN "%s"
				WHEN 7 THEN "%s" END) AS weekday_begin
			, CONCAT(CASE DAYOFWEEK(date_end) WHEN 1 THEN "%s"
				WHEN 2 THEN "%s"
				WHEN 3 THEN "%s"
				WHEN 4 THEN "%s"
				WHEN 5 THEN "%s"
				WHEN 6 THEN "%s"
				WHEN 7 THEN "%s" END) AS weekday_end
			, main_event_id
		FROM events
		LEFT JOIN categories
			ON events.event_category_id = categories.category_id
		WHERE categories.main_category_id = %d
		AND identifier = "%d/%s"
		AND %s
	';
	$sql = sprintf($sql
		, wrap_text('Su'), wrap_text('Mo'), wrap_text('Tu'), wrap_text('We') 
		, wrap_text('Th'), wrap_text('Fr'), wrap_text('Sa')
		, wrap_text('Su'), wrap_text('Mo'), wrap_text('Tu'), wrap_text('We') 
		, wrap_text('Th'), wrap_text('Fr'), wrap_text('Sa')
		, wrap_category_id('events')
		, $params[0], wrap_db_escape($params[1])
		, $published
	);
	$event = wrap_db_fetch($sql);
	$event = wrap_translate($event, 'events');

	if (!$event) {
		$event['not_found'] = true;
		$page['text'] = wrap_template('event', $event);
		$page['status'] = 404;
		return $page;
	}
	
	if ($event['main_event_id']) {
		$sql = 'SELECT event_id, event, identifier
			, CONCAT(date_begin, IFNULL(CONCAT("/", date_end), "")) AS duration
			, TIME_FORMAT(time_begin, "%%H.%%i") AS time_begin
			, TIME_FORMAT(time_end, "%%H.%%i") AS time_end
			FROM events
			WHERE event_id = %d';
		$sql = sprintf($sql, $event['main_event_id']);
		$event['events'] = wrap_db_fetch($sql, 'event_id');
	}

	$lightbox = false;
	$event['timetable'] = mod_events_event_timetable($event['event_id']);
	if ($event['timetable']) {
		$template = 'timetable';
		foreach ($event['timetable'] as $day => $timetable) {
			foreach ($timetable['hours'] as $timetable_event_id => $single_event) {
				if ($single_event['event_category_id'] === wrap_category_id('veranstaltung/veranstaltung')) {
					$template = 'timetable-programme';
				}
				if (empty($single_event['images'])) continue;
				$lightbox = true;
			}
		}
		$event['timetable'] = wrap_template($template, $event['timetable']);
	} else {
		$event['timetable'] = '';
	}

	if (strstr($event['description'], '%%% timetable %%%')) {
		$event['description'] = str_replace('%%% timetable %%%', $event['timetable'], $event['description']);
		unset ($event['timetable']);
	}

	$event['places'] = [];
/*	
	$sql = 'SELECT place_id, place, sequence, latitude, longitude
		FROM places
		LEFT JOIN events_places USING (place_id)
		WHERE event_id = %d
		ORDER BY sequence';
	$sql = sprintf($sql, $event['event_id']);
	$event['places'] = wrap_db_fetch($sql, 'place_id');
	if ($event['places']) {
		$event['map'] = wrap_template('places-geojson', $event['places']);
		$event['map'] .= wrap_template('leaflet');
	}
*/

	$media = wrap_get_media($event['event_id'], 'events', 'event');
	if (!empty($media['links'])) {
		$event['links'] = wrap_template('filelinks', $media['links']);
	}
	if (!empty($media['images'])) {
		$lightbox = true;
	}
	brick_request_links($event['description'], $media, 'sequence');
	$page['head'] = '';
	if ($lightbox) {
		$event['images'] = $media['images'];
		$page['extra']['magnific_popup'] = true;
	}

	$sql = 'SELECT article_id, title
			, identifier
			, GROUP_CONCAT(category SEPARATOR ", ") AS categories
		FROM articles
		LEFT JOIN articles_events USING (article_id)
		LEFT JOIN articles_categories USING (article_id)
		LEFT JOIN categories USING (category_id)
		WHERE published = "yes"
		AND event_id = %d
		GROUP BY article_id
		ORDER BY title';
	$sql = sprintf($sql, $event['event_id']);
	$event['articles'] = wrap_db_fetch($sql, 'article_id');
	foreach ($event['articles'] as $index => $article) {
		if ($article['categories'] !== 'Buchempfehlungen') continue;
		$event['books'][$index] = $article;
		unset($event['articles'][$index]);
	}
	
	$sql = 'SELECT category_id, category
		FROM events_categories
		LEFT JOIN categories USING (category_id)
		WHERE event_id = %d';
	$sql = sprintf($sql, $event['event_id']);
	$event['categories'] = wrap_db_fetch($sql, 'category_id');
	$event['categories'] = wrap_translate($event['categories'], 'categories');

	if (!empty($event['cancelled'])) {
		$page['status'] = 404;
	}
	$page['text'] = wrap_template('event', $event);
	if ($event['places']) {
		$page['head'] .= wrap_template('leaflet-head');
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
			, identifier
		FROM events
		WHERE published = "yes"
		AND main_event_id = %d
		ORDER BY sequence, date_begin, time_begin, time_end, sequence, identifier';
	$sql = sprintf($sql
		, wrap_text('Su'), wrap_text('Mo'), wrap_text('Tu'), wrap_text('We') 
		, wrap_text('Th'), wrap_text('Fr'), wrap_text('Sa')
		, $event_id
	);
	$events = wrap_db_fetch($sql, ['date_begin', 'event_id'], 'list date_begin hours');
	if (!$events) return [];

	// get media, set weekday
	$timetable_ids = [];
	foreach ($events as $day => $timetable) {
		$first = reset($timetable['hours']);
		$events[$day]['weekday'] = $first['weekday'];
		foreach (array_keys($timetable['hours']) as $timetable_event_id) {
			$timetable_ids[] = $timetable_event_id;
		}
	}
	$events_media = wrap_get_media($timetable_ids, 'events', 'event');
	
	// save media
	foreach ($events as $day => $timetable) {
		foreach ($timetable['hours'] as $timetable_event_id => $single_event) {
			if (!array_key_exists($timetable_event_id, $events_media)) continue;
			if (empty($events_media[$timetable_event_id]['images'])) continue;
			$events[$day]['hours'][$timetable_event_id]['images']
					= $events_media[$timetable_event_id]['images'];
		}
	}

	$events = array_values($events);
	return $events;
}