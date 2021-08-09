<?php 

/**
 * events module
 * get event data per ID
 *
 * Part of »Zugzwang Project«
 * http://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020-2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * get single event
 *
 * @param int $event_id
 * @return array
 */
function mod_events_get_event($event_id) {
	require_once __DIR__.'/eventdata.inc.php';
	$event = mod_events_get_eventdata([$event_id => ['event_id' => $event_id]]);
	$event = reset($event);

	// main event?
	if ($event['main_event_id']) {
		$sql = 'SELECT event_id, event, identifier
				, CONCAT(date_begin, IFNULL(CONCAT("/", date_end), "")) AS duration
				, TIME_FORMAT(time_begin, "%%H.%%i") AS time_begin
				, TIME_FORMAT(time_end, "%%H.%%i") AS time_end
			FROM events
			WHERE event_id = %d';
		$sql = sprintf($sql, $event['main_event_id']);
		$event['events'] = wrap_db_fetch($sql, 'event_id');
		$event['events'] = wrap_translate($event['events'], 'events');

		// categories
		$categories = mod_events_get_event_categories(array_keys($event['events']));
		foreach ($categories as $main_event_id => $event_categories) {
			$event['events'][$main_event_id]['categories'] = $event_categories;
		}
	}

	// articles?
	// @todo solve GROUP_CONCAT differently for translations
	// @todo use entry in categories.parameters to determine if an article
	// needs to be saved under a different key (e. g. books)
	$sql = 'SELECT article_id, title
			, identifier
			, GROUP_CONCAT(category SEPARATOR ", ") AS categories
			, date
		FROM articles
		LEFT JOIN articles_events USING (article_id)
		LEFT JOIN articles_categories USING (article_id)
		LEFT JOIN categories USING (category_id)
		WHERE published = "yes"
		AND event_id = %d
		AND date <= CURDATE()
		AND (ISNULL(date_to) OR date_to >= CURDATE())
		GROUP BY article_id
		ORDER BY date DESC, title';
	$sql = sprintf($sql, $event['event_id']);
	$event['articles'] = wrap_db_fetch($sql, 'article_id');
	foreach ($event['articles'] as $index => $article) {
		if ($article['categories'] !== 'Buchempfehlungen') continue;
		$event['books'][$index] = $article;
		unset($event['articles'][$index]);
	}

	return $event;
}

/**
 * get categories for a list of event IDs
 *
 * @param array $event_ds
 * @return array
 */
function mod_events_get_event_categories($event_ids) {
	$sql = 'SELECT event_category_id, event_id, category_id, category
		FROM events_categories
		LEFT JOIN categories USING (category_id)
		WHERE event_id IN (%d)';
	$sql = sprintf($sql, implode(',', $event_ids));
	$data = wrap_db_fetch($sql, 'event_category_id');
	$data = wrap_translate($data, 'categories', 'category_id');
	$categories = [];
	foreach ($data as $line) {
		unset($line['event_category_id']);
		$categories[$line['event_id']][$line['category_id']] = $line;
	}
	return $categories;
}

/**
 * get a timetable for an event
 *
 * @param int $event_id
 * @param string $lang (optional)
 * @return array
 */
function mod_events_get_event_timetable($event_id, $lang = false) {
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
			, DAYOFWEEK(date_begin) AS weekday_begin
			, event_category_id AS category_id
			, IF(event_category_id = %d, identifier, NULL) AS identifier
		FROM events
		WHERE %s
		AND main_event_id = %d
		ORDER BY sequence, date_begin, time_begin, time_end, identifier';
	$sql = sprintf($sql
		, wrap_category_id('event/event')
		, $published
		, $event_id
	);
	$events_db = wrap_db_fetch($sql, 'event_id');
	$events_db = wrap_translate($events_db, 'events');
	$events_db = wrap_weekdays($events_db, ['weekday_begin'], $lang);
	if (!$events_db) return [];

//	$events_db = mod_events_get_eventdata($events_db);

	// get media, set weekday
	$events = [];
	foreach ($events_db as $event_id => $event) {
		$day = $event['date_begin'];
		$events[$day]['date_begin'] = $day;
		$events[$day]['weekday_begin'] = $event['weekday_begin'];
		$events[$day]['hours'][$event_id] = $event;
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
