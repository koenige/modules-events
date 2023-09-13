<?php 

/**
 * events module
 * get event data per ID
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020-2021, 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * get single event
 *
 * @param int $event_id
 * @return array
 */
function mod_events_get_event($event_id, $settings = []) {
	require_once __DIR__.'/eventdata.inc.php';
	$event = mod_events_get_eventdata([$event_id => ['event_id' => $event_id]], $settings);
	if (!$event) return [];
	$event = reset($event);

	// main event?
	if ($event['main_event_id'])
		$event['events'] = mod_events_get_eventdata([$event['main_event_id'] => ['event_id' => $event['main_event_id']]], $settings);

	// news?
	if (in_array('news', wrap_setting('modules')))
		$event = mod_events_get_event_news($event);

	return $event;
}

/**
 * get associated articles and books per event
 *
 * @param array $event
 * @return array
 */
function mod_events_get_event_news($event) {
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
 * get a timetable for an event
 *
 * @param int $event_id
 * @param string $lang (optional)
 * @return array
 */
function mod_events_get_event_timetable($event_id, $lang = false) {
	if (wrap_setting('local_access') OR !empty($_SESSION['logged_in']))
		$published = '(published = "yes" OR published = "no")';
	else
		$published = 'published = "yes"';

	$sql = 'SELECT event_id FROM events
		WHERE %s
		AND main_event_id = %d
		ORDER BY sequence, date_begin, time_begin, time_end, identifier';
	$sql = sprintf($sql, $published, $event_id);
	$events_db = wrap_db_fetch($sql, 'event_id');
	if (!$events_db) return [];
	$events_db = mod_events_get_eventdata($events_db);

	// get media, set weekday
	$events = [];
	foreach ($events_db as $event_id => $event) {
		$day = $event['date_begin'];
		$events[$day]['date_begin'] = $day;
		$events[$day]['weekday_begin'] = $event['weekday_begin'];
		$events[$day]['hours'][$event_id] = $event;
	}

	$events = array_values($events);
	$events['images'] = [];
	foreach ($events_db as $event_id => $event) {
		if (empty($event['images'])) continue;
		$events['images'] += $event['images'];
	}
	return $events;
}
