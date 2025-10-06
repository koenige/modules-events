<?php 

/**
 * events module
 * output of an events calendar
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2014-2018, 2020-2025 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * Output of events
 *
 * @param array $params
 *		-	all events from today on
 *		[0]	numeric: year
 *		[0] current: all events from now on, only limit
 *		[0] organisation: all events of an organisation
 * @param array $settings
 *		int 'limit' (only if current is present)
 *		mixed 'category'
 *		bool 'archive'
 *		bool 'no404'
 * @return array
 */
function mod_events_events($params, $settings) {
	global $zz_page;
	if (count($params) === 2)
		return brick_format('%%% request event '.implode(' ', $params).' %%%');

	$limit = false;
	$current = false;
	$condition = [];
	$condition[] = 'events.event_category_id = /*_ID categories event/event _*/';
	$join = [];
	if (empty($params)) {
		if (empty($settings['archive'])) {
			$condition[] = 'date_begin >= CURDATE() OR date_end >= CURDATE()';
			$current = true;
		}
	} elseif (is_numeric($params[0])) {
		$year = $params[0];
		if (count($params) > 2) return false;
		$condition[] = sprintf('(YEAR(date_begin) = %d OR YEAR(date_end) = %d)', $year, $year);
		$page['breadcrumbs'][]['title'] = $year;
		$zz_page['db']['title'] .= ' '.$year;
	} elseif ($params[0] !== 'current' AND wrap_package('contacts')) {
		// Organisation?
		$sql = 'SELECT contact_id FROM contacts
			WHERE identifier = "%s"';
		$sql = sprintf($sql, wrap_db_escape($params[0]));
		$contact_id = wrap_db_fetch($sql, '', 'single value');
		if (!$contact_id) return false;
		$join[] = 'LEFT JOIN events_contacts USING (event_id)';
		$condition[] = sprintf('contact_id = %d', $contact_id);
	} elseif (count($params) > 2)  {
		return false;
	} else {
		$limit = $settings['limit'] ?? 3;
		$page['dont_show_h1'] = true;
		if (empty($settings['archive'])) {
			// current
			$condition[] = 'date_begin >= CURDATE() OR date_end >= CURDATE()';
			$current = true;
		}
		$condition[] = 'takes_place = "yes"';
	}
	if (!empty($settings['category'])) {
		if (!is_array($settings['category'])) $settings['category'] = [$settings['category']];
		$join[] = 'LEFT JOIN events_categories USING (event_id)';
		$categories = [];
		foreach ($settings['category'] as $path) {
			$categories[] = sprintf('events_categories.category_id = /*_ID categories events/%s _*/', $path);
		}
		$condition[] = implode(' OR ', $categories);
	}
	
	if (empty($_SESSION['logged_in'])) {
		$condition[] = 'events.published = "yes"';
	}
	
	$sort = $settings['sort'] ?? NULL;
	if (!in_array($sort, ['ASC', 'DESC'])) $sort = NULL;
	
	$sql = 'SELECT event_id
	    FROM events
	    %s
		WHERE (%s)
		ORDER BY events.date_begin %s, events.date_end, events.time_begin
		%s
	';
	$sql = sprintf($sql
		, implode(' ', $join)
		, implode(') AND (', $condition)
		, $sort ?? ($current ? 'ASC' : 'DESC')
		, $limit ? sprintf(' LIMIT %d', $limit) : ''
	);
	$events = wrap_db_fetch($sql, 'event_id');
	
	wrap_include('data', 'zzwrap');
	$events = wrap_data('events', $events);

	if (!$events) {
		$events['no_events'] = true;
		if ((empty($params) OR $params[0] !== 'current') AND empty($settings['no404']))
			$page['status'] = 404;
	} else {
		foreach ($events as $event_id => $event) {
			if (empty($event['images'])) continue;
			if (in_array('magnificpopup', wrap_setting('modules')))
				$page['extra']['magnific_popup'] = true;
			break;
		}
		$page['status'] = 200;
	}

	$events['cal_title'] = '';
	$template = $settings['template'] ?? 'events';
	$page['text'] = wrap_template($template, $events);
	return $page;
}
