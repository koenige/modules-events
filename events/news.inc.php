<?php 

/**
 * events module
 * functions for news module
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2022 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * get events displayed inside news
 *
 * @param string $type (optional) 'all', 'rss', 'latest'
 * @return array
 */
function mf_events_in_news($type = 'all') {
	global $zz_setting;

	$sql = 'SELECT event_id
			, DATE_FORMAT(created, "%%Y-%%m-%%d") AS date
			, DATE_FORMAT(created, "%%H:%%i") AS time
			, identifier, abstract, event AS title
			, direct_link
			, IFNULL(direct_link, CONCAT("%s/", identifier, "/")) AS link
			, CONCAT(date_begin, IFNULL(CONCAT("/", date_end), "")) AS duration
			, DATE_FORMAT(events.last_update, "%%a, %%d %%b %%Y %%H:%%i:%%s") AS pubDate
			, CONCAT("%s/", identifier, "/") AS guid
		FROM events
		WHERE IF(ISNULL(date_end), date_begin >= CURDATE(), date_end >= CURDATE())
		AND published = "yes"
		AND ISNULL(main_event_id)';
	$sql = sprintf($sql, $zz_setting['events_path'], $zz_setting['events_path']);
	
	switch ($type) {
	case 'all':
		$sql .= 'AND show_in_news = "yes"
			ORDER BY created DESC, identifier DESC';
		break;
	case 'rss':
		$sql .= 'ORDER BY created DESC, identifier DESC';
		break;
	case 'latest':
		$sql .= 'ORDER BY date_begin ASC, time_begin ASC
			LIMIT 1';
		break;
	}
	$events = wrap_db_fetch($sql, 'event_id');
	if (!$events) return [];

	$events = wrap_translate($events, 'events');
	$media = wrap_get_media(array_keys($events), 'events', 'event');
	foreach ($media as $event_id => $files) {
		if (!empty($files['images'])) {
			$events[$event_id]['images'] = $files['images'];
		}
		if (!empty($files['links'])) {
			$events[$event_id]['links'] = $files['links'];
		}
	}
	return $events;
}

/**
 * sort news and events by date, time
 *
 * @param array $articles
 * @param array $events
 * @return array
 */
function mf_events_in_news_sort($articles, $events) {
	if (!$articles) return $events;
	if (!$events) return $articles;

	$articles += $events;
	$sort_dates = [];
	$sort_times = [];
	$sort_identifiers = [];
	foreach ($articles as $article) {
		$sort_dates[] = $article['date'];
		$sort_times[] = $article['time'];
		$sort_identifiers[] = $article['identifier'];
	}
	array_multisort($sort_dates, SORT_DESC, $sort_times, SORT_DESC, $sort_identifiers, SORT_ASC, $articles);
	return $articles;
}
