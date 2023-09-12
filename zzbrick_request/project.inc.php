<?php 

/**
 * events module
 * output of a single project
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_events_project($params) {
	$published = empty($_SESSION['logged_in']) ? 'AND events.published = "yes"' : '';
	$image_no = mod_events_project_image_no($params);

	$sql = 'SELECT event_id
	    FROM events
	    WHERE identifier = "%s"
	    AND event_category_id = %d
	    %s';
	$sql = sprintf($sql
		, wrap_db_escape(implode('/', $params))
		, wrap_category_id('event/project')
		, $published
	);
	$event = wrap_db_fetch($sql);

	$page['dont_show_h1'] = true;
	
	if (count($event) !== 1) {
		$event['not_found'] = true;
		$page['text'] = wrap_template('project', $event);
		$page['status'] = 404;
		return $page;
	}

	wrap_include_files('zzbrick_request_get/event', 'events');
	$event = mod_events_get_event($event['event_id'], ['category' => 'project']);
	
	if (wrap_setting('events_project_image_pages') AND !empty($event['images'])) {
		$event += mod_events_project_links($event, $image_no);
		if ($image_no) {
			foreach ($event['images'] as $medium_id => $medium)
				if ($image_no.'' !== $medium['sequence'].'') unset($event['images'][$medium_id]);
			if (!$event['images']) return false;
		}
		$page['link'] = wrap_page_links($event, 'events_project');
	} elseif ($image_no) {
		return false;
	}
	
	$lightbox = false;

	if (wrap_setting('events_leaflet_map') AND !empty($event['location'])) {
		$event['places'] = [];
		foreach ($event['location'] as $event_contact_id => $location) {
			if (empty($location['latitude'])) continue;
			if (empty($location['longitude'])) continue;
			$event['places'][$event_contact_id] = $location;
		}
		if ($event['places']) {
			$event['map'] = wrap_template('places-geojson', $event['places']);
			$event['map'] .= wrap_template('leaflet');
			$page['head'] = wrap_template('leaflet-head');
		}
	}

	foreach ($event as $field => $values) {
		if (!is_array($values)) continue;
		foreach ($values as $index => $value) {
			if (empty($value['contact_path'])) continue;
			$event[$field][$index][$value['contact_path']] = true;	
		}
	}

	if (!empty($event['links']))
		$event['links'] = wrap_template('filelinks', $event['links']);
	if (!empty($event['images']))
		$lightbox = true;
	if ($lightbox)
		$page['extra']['magnific_popup'] = true;
	brick_request_links($event['description'], $event, 'sequence');
	
	if (!empty($event['cancelled'])) {
		$page['status'] = 410;
	}
	$page['text'] = wrap_template('project', $event);
	$page['meta'] = [
		0 => ['property' => 'og:url', 'content' => wrap_setting('host_base').wrap_setting('request_uri')],
		1 => ['property' => 'og:type', 'content' => 'article'],
		2 => ['property' => 'og:title', 'content' => wrap_html_escape(strip_tags($event['event']))],
		3 => ['property' => 'og:description', 'content' => wrap_html_escape(trim(strip_tags(markdown($event['abstract']))))]
	];
	if (!empty($event['images'])) {
		$main_img = reset($event['images']);
		$page['meta'][] 
			= ['property' => 'og:image', 'content' => wrap_setting('host_base').wrap_setting('files_path').'/'.$main_img['filename'].'.'.wrap_setting('events_og_image_size').'.'.$main_img['thumb_extension'].'?v='.$main_img['version']];
	}
	$page['title'] = $event['event'].', '.wrap_date($event['duration']).($image_no ? ', '.wrap_text('Image %d', ['values' => $image_no]) : '');
	$page['breadcrumbs'][] = '<a href="'.wrap_path('events_event', $event['year']).'">'.$event['year'].'</a>';
	if ($image_no) {
		$page['breadcrumbs'][] = '<a href="../">'.$event['event'].'</a>';
		$page['breadcrumbs'][]['title'] = $image_no;
	} else {
		$page['breadcrumbs'][]['title'] = $event['event'];
	}
	
	if (!$event['published'])
		$page['extra']['body_attributes'] = ' class="unpublished"';
	return $page;
}

/**
 * get last parameter, check if it is an image number
 *
 * @param array $params
 * @return int
 */
function mod_events_project_image_no(&$params) {
	if (!wrap_setting('events_project_image_pages')) return NULL;
	if (count($params) === 1) return NULL;
	if (!is_numeric(end($params))) return NULL;
	$image_no = array_pop($params);
	if (intval($image_no).'' !== $image_no.'') return false;
	$image_no = intval($image_no);
	if ($image_no === 1) wrap_redirect('../');
	return $image_no;
}		

/**
 * get correct image
 *
 * @param array $params
 * @return array
 */
function mod_events_project_links($event, $image_no) {
	if (!$image_no) {
		$links['_next_identifier'] = sprintf('%s/%d', $event['identifier'], 2);
		$links['_next_title'] = 2;
	} elseif ($image_no === count($event['images'])) {
		$links['_next_identifier'] = $event['identifier'];
		$links['_next_title'] = 1;
	} else {
		$links['_next_identifier'] = sprintf('%s/%d', $event['identifier'], $image_no + 1);
		$links['_next_title'] = $image_no + 1;
	}
	if (!$image_no) {
		$links['_prev_identifier'] = sprintf('%s/%d', $event['identifier'], count($event['images']));
		$links['_prev_title'] = count($event['images']);
	} elseif ($image_no === 2) {
		$links['_prev_identifier'] = $event['identifier'];
		$links['_prev_title'] = 1;
	} else {
		$links['_prev_identifier'] = sprintf('%s/%d', $event['identifier'], $image_no - 1);
		$links['_prev_title'] = $image_no - 1;
	}
	return $links;
}
