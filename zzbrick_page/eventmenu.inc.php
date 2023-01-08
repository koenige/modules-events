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
	// @todo not active yet
	return '';
	
	if (empty($zz_page['db'])) return '';
	$params = explode('/', $zz_page['db']['parameter']);
	if (count($params) < 2) return '';

	$sql = 'SELECT eventmenu_id
			, menu, path, parameters
			, IFNULL(event_year, YEAR(date_begin)) AS year
		FROM eventmenus
		LEFT JOIN events USING (event_id)
		WHERE identifier = "%d/%s"
		ORDER BY eventmenus.sequence';
	$sql = sprintf($sql, $params[0], wrap_db_escape($params[1]));
	$data = wrap_db_fetch($sql, 'eventmenu_id');
	if (!$data) return '';

	$placeholder_keys = ['menu', 'path'];
	foreach ($data as &$line) {
		foreach ($placeholder_keys as $key) {
			if (strstr($line[$key], '%%% year %%%'))
				$line[$key] = str_replace('%%% year %%%', $line['year'], $line[$key]);
		}
	}
	
	return wrap_template('eventmenu', $data);
}
