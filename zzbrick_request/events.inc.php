<?php 

/**
 * events module
 * output of an events calendar
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2014-2018, 2020 Gustaf Mossakowski
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
	$box = false;
	$condition = '';
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
		$box = true;
	}

	if ($zz_setting['local_access']) {
		$published = '(events.published = "yes" OR events.published = "no")';
	} else {
		$published = 'events.published = "yes"';
	}
	
	$sql = 'SELECT DISTINCT event_id, identifier
			, event, abstract, events.description, date_begin, date_end
			, CONCAT(date_begin, IFNULL(CONCAT("/", date_end), "")) AS duration
			, TIME_FORMAT(time_begin, "%%H.%%i") AS time_begin
			, time_begin AS time_begin_iso
			, TIME_FORMAT(time_end, "%%H.%%i") AS time_end
			, time_end AS time_end_iso
			, IF(date_begin >= CURDATE() OR date_end >= CURDATE(), "Aktuelle Termine", "Vergangene Termine") AS terminstatus
			, IF(takes_place = "yes", NULL, 1) AS cancelled
			, (CASE DAYOFWEEK(date_begin) WHEN 1 THEN "%s"
				WHEN 2 THEN "%s"
				WHEN 3 THEN "%s"
				WHEN 4 THEN "%s"
				WHEN 5 THEN "%s"
				WHEN 6 THEN "%s"
				WHEN 7 THEN "%s" END) AS weekday_begin
			, (CASE DAYOFWEEK(date_end) WHEN 1 THEN "%s"
				WHEN 2 THEN "%s"
				WHEN 3 THEN "%s"
				WHEN 4 THEN "%s"
				WHEN 5 THEN "%s"
				WHEN 6 THEN "%s"
				WHEN 7 THEN "%s" END) AS weekday_end
			, category
			, timezone
		FROM events
		LEFT JOIN events_contacts USING (event_id)
		LEFT JOIN categories
			ON categories.category_id = events.event_category_id
		WHERE %s
		AND event_category_id = %d
		%s
		ORDER BY events.date_begin %s
		%s
	';
	$sql = sprintf($sql
		, wrap_text('Sun'), wrap_text('Mon'), wrap_text('Tue'), wrap_text('Wed') 
		, wrap_text('Thu'), wrap_text('Fri'), wrap_text('Sat')
		, wrap_text('Sun'), wrap_text('Mon'), wrap_text('Tue'), wrap_text('Wed') 
		, wrap_text('Thu'), wrap_text('Fri'), wrap_text('Sat')
		, $published
		, wrap_category_id('event/event')
		, $condition
		, $current ? 'ASC' : 'DESC'
		, $limit ? sprintf(' LIMIT %d', $limit) : ''
	);
	$events = wrap_db_fetch($sql, 'event_id');

	$images = false;
	if ($events) {
		$media = wrap_get_media(array_keys($events), 'events', 'event');
		foreach ($media as $event_id => $files) {
			if (!empty($files['links'])) {
				$events[$event_id]['links'] = $files['links'];
			}
			if (!empty($files['images'])) {
				$events[$event_id]['images'] = $files['images'];
				$images = true;
			}
		}

		$sql = 'SELECT event_id, category_id, category
			FROM events_categories
			LEFT JOIN categories USING (category_id)
			WHERE event_id IN (%s)';
		$sql = sprintf($sql, implode(',', array_keys($events)));
		$categories = wrap_db_fetch($sql, ['event_id', 'category_id']);
		foreach ($categories as $event_id => $event_category) {
			$events[$event_id]['categories'] = $event_category;
			foreach ($event_category as $subcategory) {
				if ($subcategory['category'] === 'Reise') $events[$event_id]['reise'] = true;
			}
		}

	} else {
		$media = [];
		$events['no_events'] = true;
	}
	if ($images) {
		$page['extra']['magnific_popup'] = true;
	}

	$events['cal_title'] = '';
	$template = $box ? 'eventbox' : 'events';
	$template = 'events';
	$page['text'] = wrap_template($template, $events);
	if (in_array('magnificpopup', $zz_setting['modules']))
		$page['extra']['magnific_popup'] = true;
	return $page;
}
