<?php 

/**
 * events module
 * output of a single event
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2014-2022 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_events_event($params) {
	global $zz_setting;

	if (count($params) !== 2) return false;

	if ($zz_setting['local_access'] OR !empty($_SESSION['logged_in'])) {
		$published = '(events.published = "yes" OR events.published = "no")';
		$zz_setting['cache'] = false;
	} else {
		$published = 'events.published = "yes"';
	}
	
	$sql = 'SELECT event_id
	    FROM events
	    WHERE identifier = "%d/%s"
	    AND event_category_id = %d
	    AND %s';
	$sql = sprintf($sql
		, $params[0], wrap_db_escape($params[1])
		, wrap_category_id('event/event')
		, $published
	);
	$event = wrap_db_fetch($sql);
	
	if (count($event) !== 1) {
		$event['not_found'] = true;
		$page['text'] = wrap_template('event', $event);
		$page['status'] = 404;
		return $page;
	}

	require_once __DIR__.'/../zzbrick_request_get/event.inc.php';
	$event = mod_events_get_event($event['event_id']);
	
	$lightbox = false;
	$event['timetable'] = mod_events_get_event_timetable($event['event_id']);
	if ($event['timetable']) {
		foreach ($event['timetable'] as $day => $timetable) {
			if (!is_numeric($day)) continue; // images
			foreach ($timetable['hours'] as $timetable_event_id => $single_event) {
				if ($single_event['category_id'] === wrap_category_id('event/event')) {
					$event['timetable']['programme'] = true;
				}
				if (empty($single_event['images'])) continue;
				$lightbox = true;
			}
		}
		if (!empty($event['past_event'])) $event['timetable']['past_event'] = true;
		$event['timetable'] = wrap_template('timetable', $event['timetable']);
	} else {
		$event['timetable'] = '';
	}

	$timetable_placeholder = '%%% '.wrap_get_setting('events_timetable_placeholder').' %%%';
	if (strstr($event['description'], $timetable_placeholder)) {
		$event['description'] = str_replace($timetable_placeholder, $event['timetable'], $event['description']);
		unset ($event['timetable']);
	}
	
	if (wrap_get_setting('events_leaflet_map') AND !empty($event['location'])) {
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

	if (!empty($event['links'])) {
		$event['links'] = wrap_template('filelinks', $event['links']);
	}
	if (!empty($event['images'])) {
		$lightbox = true;
	}
	if ($lightbox) {
		$page['extra']['magnific_popup'] = true;
	}
	brick_request_links($event['description'], $event, 'sequence');
	
	if (!empty($event['cancelled'])) {
		$page['status'] = 410;
	}
	$page['text'] = wrap_template('event', $event);
	$page['meta'] = [
		0 => ['property' => 'og:url', 'content' => $zz_setting['host_base'].$zz_setting['request_uri']],
		1 => ['property' => 'og:type', 'content' => 'article'],
		2 => ['property' => 'og:title', 'content' => wrap_html_escape(strip_tags($event['event']))],
		3 => ['property' => 'og:description', 'content' => wrap_html_escape(trim(strip_tags(markdown($event['abstract']))))]
	];
	if (!empty($event['images'])) {
		$main_img = reset($event['images']);
		$page['meta'][] 
			= ['property' => 'og:image', 'content' => $zz_setting['host_base'].$zz_setting['files_path'].'/'.$main_img['filename'].'.'.wrap_get_setting('news_og_image_size').'.'.$main_img['thumb_extension'].'?v='.$main_img['version']];
	}
	$page['title'] = $event['event'].', '.wrap_date($event['duration']);
	$page['breadcrumbs'][] = '<a href="'.$zz_setting['events_path'].'/'.$event['year'].'/">'.$event['year'].'</a>';
	$page['breadcrumbs'][] = $event['event'];
	$page['dont_show_h1'] = true;
	return $page;
}
