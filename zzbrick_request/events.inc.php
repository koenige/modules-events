<?php 

/**
 * events module
 * output of an events calendar
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2014-2018, 2020-2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * Output of events
 *
 * @param array $params
 *		-	all events from today on
 *		[0]	numeric: year
 *		[0] current: all events from now on, only limit
 *		[0] organisation: alle events of an organisation
 * @param array $settings
 *		int 'limit' (only if current is present)
 * @return array
 */
function mod_events_events($params, $settings) {
	global $zz_setting;

	if (count($params) === 2)
		return brick_format('%%% request event '.implode(' ', $params).' %%%');

	$limit = false;
	$year = false;
	$current = false;
	$condition = '';
	$join = '';
	if (empty($params)) {
		$condition = 'AND date_begin >= CURDATE()';
		$current = true;
	} elseif (is_numeric($params[0])) {
		$year = $params[0];
		if (count($params) > 2) return false;
		$condition = sprintf('AND (YEAR(date_begin) = %d OR YEAR(date_end) = %d)', $year, $year);
		$page['breadcrumbs'][] = $year;
	} elseif ($params[0] !== 'current') {
		// Organisation?
		$sql = 'SELECT contact_id FROM contacts
			WHERE identifier = "%s"';
		$sql = sprintf($sql, wrap_db_escape($params[0]));
		$contact_id = wrap_db_fetch($sql, '', 'single value');
		if (!$contact_id) return false;
		$join = 'LEFT JOIN events_contacts USING (event_id)';
		$condition = sprintf(' AND contact_id = %d', $contact_id);
	} elseif (count($params) > 2)  {
		return false;
	} else {
		// current
		$limit = isset($settings['limit']) ? $settings['limit'] : 3;
		$page['dont_show_h1'] = true;
		$condition = ' AND (date_begin >= CURDATE() OR date_end >= CURDATE())
		AND takes_place = "yes"';
		$current = true;
	}

	if ($zz_setting['local_access']) {
		$published = '(events.published = "yes" OR events.published = "no")';
	} else {
		$published = 'events.published = "yes"';
	}
	
	$sql = 'SELECT event_id
			, IF(date_begin >= CURDATE() OR date_end >= CURDATE(), "Aktuelle Termine", "Vergangene Termine") AS terminstatus
	    FROM events
	    %s
		WHERE %s
		AND event_category_id = %d
		%s
		ORDER BY events.date_begin %s
		%s
	';
	$sql = sprintf($sql
		, $join
		, $published
		, wrap_category_id('event/event')
		, $condition
		, $current ? 'ASC' : 'DESC'
		, $limit ? sprintf(' LIMIT %d', $limit) : ''
	);
	$events = wrap_db_fetch($sql, 'event_id');
	
	require_once __DIR__.'/../zzbrick_request_get/eventdata.inc.php';
	$events = mod_events_get_eventdata($events);

	if (!$events) {
		$events['no_events'] = true;
	} else {
		foreach ($events as $event_id => $event) {
			if (empty($event['images'])) continue;
			if (in_array('magnificpopup', $zz_setting['modules']))
				$page['extra']['magnific_popup'] = true;
			break;
		}
	}

	$events['cal_title'] = '';
	$template = !empty($settings['template']) ? $settings['template'] : 'events';
	$page['text'] = wrap_template($template, $events);
	return $page;
}
