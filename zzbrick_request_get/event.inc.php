<?php 

/**
 * events module
 * get event data per ID
 *
 * Part of »Zugzwang Project«
 * http://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * get single event
 *
 * @param int $event_id
 * @return array
 */
function mod_events_get_event($event_id) {
	require_once __DIR__.'/../zzbrick_request_get/events.inc.php';

	$event = mod_events_get_events([$event_id => ['event_id' => $event_id]]);
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
