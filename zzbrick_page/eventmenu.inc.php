<?php 

/**
 * events module
 * output of an event menu
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2022-2023 Gustaf Mossakowski
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
		FROM eventmenus
		LEFT JOIN events
			ON eventmenus.event_id = IFNULL(events.main_event_id, events.event_id)
		WHERE events.identifier = "%d/%s"
		ORDER BY eventmenus.sequence';
	$sql = sprintf($sql, $params[0], wrap_db_escape($params[1]));
	$data = wrap_db_fetch($sql, 'eventmenu_id');
	if (!$data) return '';

	$placeholder_keys = ['menu', 'path'];

	foreach ($data as $eventmenu_id => $line) {
		// remove anchor for comparison
		if ($pos = strpos($line['path'], '#'))
			$line['path'] = substr($line['path'], 0, $pos);

		// check if menu is active or below
		if ($line['path'] === wrap_setting('request_uri'))	
			$data[$eventmenu_id]['path'] = false;
		elseif (str_starts_with(wrap_setting('request_uri'), $line['path']))
			$data[$eventmenu_id]['below'] = true;
		
		// placeholders
		foreach ($placeholder_keys as $key) {
			if (strstr($line[$key], '%%% year %%%'))
				$data[$eventmenu_id][$key] = str_replace('%%% year %%%', $line['year'], $line[$key]);
		}
	}
	
	return wrap_template('eventmenu', $data);
}
