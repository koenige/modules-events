<?php 

/**
 * events module
 * output of a project list
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023-2024 Gustaf Mossakowski
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

	$conditions[] = 'events.event_category_id = /*_ID categories event/project _*/';

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
	$data = wrap_db_fetch($sql, 'event_id');
	
	wrap_include('data', 'zzwrap');
	$data = wrap_data('events', $data, ['category' => 'project']);

	if (!$data) {
		$data['no_projects'] = true;
		$page['status'] = 404;
	} else {
		foreach ($data as $event_id => $event) {
			if (empty($event['images'])) continue;
			if (in_array('magnificpopup', wrap_setting('modules')))
				$page['extra']['magnific_popup'] = true;
			break;
		}
		$page['status'] = 200;
	}
	
	$data += mod_events_projects_categories($data);

	$template = !empty($settings['template']) ? $settings['template'] : 'projects';
	$page['text'] = wrap_template($template, $data);
	return $page;
}

/**
 * get list of project categories
 *
 * @param array $data
 * @return array
 */
function mod_events_projects_categories($data) {
	$sql = 'SELECT category_id, category, SUBSTRING_INDEX(path, "/", -1) AS path
			, description
		FROM categories
		WHERE main_category_id = /*_ID categories projects _*/';
	$categories = wrap_db_fetch($sql, 'category_id');
	$sql = 'SELECT category_id, category, SUBSTRING_INDEX(path, "/", -1) AS path
			, description
		FROM categories
		WHERE main_category_id IN (%s)';
	$categories = wrap_db_children($categories, $sql, 'category_id', 'main_category_id');
	if (!$categories) return [];
	
	$paths = [];
	foreach ($categories[0] as $category_id => $category) {
		$key = sprintf('categories_%s', $category['path']);
		$paths[] = $category['path'];
		$data[$key] = $categories[$category_id] ?? [];
	}
	foreach ($data as $event_id => $project) {
		if (!is_numeric($event_id)) continue;
		foreach ($paths as $path) {
			if (!array_key_exists($path, $project)) continue;
			if (!is_array($project[$path])) continue;
			$key = sprintf('categories_%s', $path);
			foreach (array_keys($project[$path]) as $category_id) {
				if (empty($data[$key][$category_id]['projects']))
					$data[$key][$category_id]['projects'] = [];
				$data[$key][$category_id]['projects'][] = $project;
			}
		}
	}
	return $data;
}
