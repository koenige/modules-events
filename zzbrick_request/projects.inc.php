<?php 

/**
 * events module
 * output of a project list
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * Output of projects
 *
 * @param array $params
 *		current: currently running
 *		path of a category: projects of this category(ies) only 
 * @param array $settings
 * @return array
 */
function mod_events_projects($params, $settings) {
	$conditions = [];
	$current = false;
	$join = '';

	$conditions[] = sprintf('events.event_category_id = %d', wrap_category_id('event/project'));

	if (count($params) === 1 AND $params[0] === 'current') {
		$conditions[] = '(date_begin >= CURDATE() OR date_end >= CURDATE())';
		$current = true;
	} elseif ($params) {
		return false;
	}

	if (!empty($settings['category'])) {
		if (!is_array($settings['category']))
			$settings['category'] = [$settings['category']];
		foreach ($settings['category'] as $index => $path) {
			$join .= sprintf(' LEFT JOIN events_categories ec_%d USING (event_id)', $index);
			if (!str_starts_with($path, 'projects/'))
				$path = sprintf('projects/%s', $path);
			$c_condition[] = sprintf('ec_%d.category_id = %d', $index, wrap_category_id($path));		
		}
		$conditions[] = sprintf('(%s)', implode(' AND ', $c_condition));
	}

	if (wrap_setting('local_access')) {
		$conditions[] = '(events.published = "yes" OR events.published = "no")';
	} else {
		$conditions[] = 'events.published = "yes"';
	}
	
	$sql = 'SELECT event_id
	    FROM events
	    %s
		WHERE %s
		ORDER BY events.date_begin %s, events.identifier
		%s
	';
	$sql = sprintf($sql
		, $join
		, implode(' AND ', $conditions)
		, $current ? 'ASC' : 'DESC'
		, isset($settings['limit']) ? sprintf(' LIMIT %d', $settings['limit']) : ''
	);
	$events = wrap_db_fetch($sql, 'event_id');
	
	require_once __DIR__.'/../zzbrick_request_get/eventdata.inc.php';
	$events = mod_events_get_eventdata($events, ['category' => 'project']);

	if (!$events) {
		$events['no_projects'] = true;
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

	$template = !empty($settings['template']) ? $settings['template'] : 'projects';
	$page['text'] = wrap_template($template, $events);
	return $page;
}
