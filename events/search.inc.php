<?php

/**
 * events module
 * search functions
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020, 2022, 2024-2025 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mf_events_search($q) {
	$where_sql = '(event LIKE "%%%s%%"
		OR abstract LIKE "%%%s%%"
		OR events.description LIKE "%%%s%%"
		OR category LIKE "%%%s%%")';
	$where = [];
	foreach ($q as $string) {
		$where[] = sprintf($where_sql, $string, $string, $string, $string);
	}
	$sql = 'SELECT events.event_id
			, CONCAT(date_begin, IFNULL(CONCAT("/", date_end), "")) AS duration
			, event, abstract, identifier
			, IF (takes_place = "yes", 1, NULL) AS takes_place
			, category
		FROM events
		LEFT JOIN events_categories
			ON events_categories.event_id = events.event_id
			AND events_categories.type_category_id = /*_ID categories events _*/
		LEFT JOIN categories USING (category_id)
		WHERE %s
		AND published = "yes"
		AND (ISNULL(categories.parameters) OR categories.parameters NOT LIKE "%%search=0%%")
		ORDER BY IFNULL(date_begin, date_end) DESC, time_begin DESC, event';
	$sql = sprintf($sql, implode(' AND ', $where));
	$data['events'] = wrap_db_fetch($sql, 'event_id');
	$data['events'] = mf_events_media($data['events']);
	return $data;
}

function mf_events_media($events) {
	if (!$events) return [];
	$media = wrap_media(array_keys($events), 'events');
	foreach ($media as $id => $files) {
		$events[$id] += $files;
	}
	return $events;
}
