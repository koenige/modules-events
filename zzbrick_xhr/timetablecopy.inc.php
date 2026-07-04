<?php

/**
 * events module
 * XHR autosuggest for timetable copy: main events matching the search query
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2026 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * @param array $request POST fields (httpRequest, limit, text, …)
 */
function mod_events_xhr_timetablecopy($request, $parameter) {
	$sql = 'SELECT event_id, website_id FROM events WHERE identifier = "%s"';
	$sql = sprintf($sql, wrap_db_escape($parameter));
	$event = wrap_db_fetch($sql);
	if (!$event)
		return brick_xhr_error(400, 'Couldn‘t find the event');

	wrap_db_charset('utf8');

	$text = trim((string) ($request['text'] ?? ''));
	$limit = (int) ($request['limit'] ?? 10);
	if ($limit < 1)
		$limit = 10;
	$limit_fetch = $limit + 1;

	if (mb_strlen($text) < 2) {
		return ['entries' => []];
	}

	$needle = mb_strtolower($text);
	$needle = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle);
	$pattern = wrap_db_escape('%'.$needle.'%');

	$sql = 'SELECT events.identifier, events.event
			, IFNULL(NULLIF(events.event_year, 0), YEAR(events.date_begin)) AS display_year
		FROM events
		WHERE events.event_category_id = /*_ID categories event/event _*/
		AND events.website_id = %u
		AND events.event_id != %u
		AND (
			events.identifier LIKE "%s"
			OR events.event LIKE "%s"
		)
		ORDER BY events.date_begin DESC, events.identifier
		LIMIT %u';
	$sql = sprintf($sql
		, $event['website_id']
		, $event['event_id']
		, $pattern
		, $pattern
		, $limit_fetch
	);
	$records = wrap_db_fetch($sql, '_dummy_', 'numeric');

	$data = [];
	if (!$records) {
		$data['entries'][] = ['text' => htmlspecialchars($text)];
		$data['entries'][] = [
			'text' => wrap_text('No record was found.'),
			'elements' => [
				0 => [
					'node' => 'div',
					'properties' => [
						'className' => 'xhr_foot',
						'text' => wrap_text('No record was found.')
					]
				]
			]
		];
		return $data;
	}
	if (count($records) > $limit) {
		$data['entries'][] = ['text' => htmlspecialchars($text)];
		$data['entries'][] = [
			'text' => wrap_text('Please enter more characters.'),
			'elements' => [
				0 => [
					'node' => 'div',
					'properties' => [
						'className' => 'xhr_foot',
						'text' => wrap_text('Please enter more characters.')
					]
				]
			]
		];
		return $data;
	}

	foreach ($records as $record) {
		$identifier = (string) $record['identifier'];
		$year = isset($record['display_year']) ? (string) $record['display_year'] : '';
		$line = $identifier.' – '.((string) $record['event']).' ('.$year.')';
		$data['entries'][] = [
			'text' => htmlspecialchars($identifier, ENT_QUOTES, 'UTF-8').' ',
			'elements' => [
				0 => [
					'node' => 'div',
					'properties' => [
						'className' => 'xhr_record',
						'text' => $line,
					]
				]
			]
		];
	}

	return $data;
}
