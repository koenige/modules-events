<?php 

/**
 * events module
 * internal page for an event
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_events_eventinternal($params, $settings, $event) {
	if (count($params) !== 2) return false;
	if (!wrap_access('events_event_edit', $event['event_rights'])) wrap_quit(403);
	
	$page['text'] = wrap_template('eventinternal', $event);
	$page['title'] = sprintf('%s: %s, %s', wrap_text('Internal'), $event['event'], wrap_date($event['duration']));
	$page['breadcrumbs'][]['title'] = strip_tags($event['event']);
	return $page;
}
