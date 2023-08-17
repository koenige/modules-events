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
	$event = mod_events_get_event($event['event_id']);
	
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
	$page['title'] = $event['event'].', '.wrap_date($event['duration']);
	$page['breadcrumbs'][] = '<a href="'.wrap_path('events_event', $event['year']).'">'.$event['year'].'</a>';
	$page['breadcrumbs'][]['title'] = $event['event'];
	if (!$event['published'])
		$page['extra']['body_attributes'] = ' class="unpublished"';
	return $page;
}
