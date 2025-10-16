<?php 

/**
 * events module
 * output of an event menu
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2022-2023, 2025 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function page_eventmenu() {
	global $zz_page;
	if (empty($zz_page['db'])) return '';
	$params = explode('/', $zz_page['db']['parameter']);
	if (count($params) < 2) return '';

	// is it an event with eventmenus?
	// check for main event having an event menu first
	$sql = 'SELECT eventmenu_id
			, menu, path, eventmenus.parameters
			, IFNULL(event_year, YEAR(date_begin)) AS year
			, eventmenus.event_id
			, website_id
		FROM eventmenus
		LEFT JOIN events
			ON eventmenus.event_id = IFNULL(events.main_event_id, events.event_id)
		WHERE events.identifier = "%d/%s"
		ORDER BY eventmenus.sequence';
	$sql = sprintf($sql, $params[0], wrap_db_escape($params[1]));
	$data = wrap_db_fetch($sql, 'eventmenu_id');
	if (!$data) return '';

	$placeholder_keys = ['menu', 'path'];

	foreach ($data as $eventmenu_id => &$line) {
		// remove anchor for comparison
		if ($pos = strpos($line['path'], '#'))
			$line['path'] = substr($line['path'], 0, $pos);

		// check parameters
		if ($line['parameters']) {
			parse_str($line['parameters'], $line['parameters']);
			if (!empty($line['parameters']['running'])) {
				if (!page_eventmenu_running($line['event_id'])) {
					unset($data[$eventmenu_id]);
					continue;
				}
			}
			if (!empty($line['parameters']['check'])) {
				if (!page_eventmenu_check($line['path'], $line['website_id'])) {
					unset($data[$eventmenu_id]);
					continue;
				}
			}
		}

		// check if menu is active or below
		if ($line['path'] === wrap_setting('request_uri'))	
			$line['path'] = false;
		elseif (str_starts_with(wrap_setting('request_uri'), $line['path']))
			$line['below'] = true;
		
		// placeholders
		foreach ($placeholder_keys as $key) {
			if (strstr($line[$key], '%%% year %%%'))
				$line[$key] = str_replace('%%% year %%%', $line['year'], $line[$key]);
		}
	}
	
	return wrap_template('eventmenu', $data);
}

/**
 * check if an event is (or associated events are) running
 *
 * @param int $event_id
 * @return bool
 */
function page_eventmenu_running($event_id) {
	static $running = [];
	if (array_key_exists($event_id, $running)) return $running[$event_id];
	
	$sql = 'SELECT event_id
		FROM events
		WHERE (event_id = %d OR main_event_id = %d)
		AND event_category_id = /*_ID categories event/event _*/
 		AND NOW() BETWEEN IFNULL(date_begin, date_begin) AND IFNULL(date_end, date_begin)';
	$sql = sprintf($sql, $event_id, $event_id);
	$events = wrap_db_fetch($sql, 'event_id', 'single value');
	$running[$event_id] = count($events) ? true : false;
	return $running[$event_id];
}

/**
 * check if a URL exists, via local cache file
 *
 * @param string $url
 * @param int $website_id
 * @return bool
 */
function page_eventmenu_check($url, $website_id) {
	static $check = [];
	if (array_key_exists($url, $check)) return $check[$url];

	$host_base = wrap_host_base($website_id);
	$cache_file = wrap_cache_filename('url', $host_base.'/'.$url);
	$check[$url] = file_exists($cache_file) ? true : false;
	return $check[$url];
}
