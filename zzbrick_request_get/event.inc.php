<?php 

/**
 * events module
 * get event data per ID
 *
 * Part of »Zugzwang Project«
 * http://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * get single event
 *
 * @param int $event_id
 * @return array
 */
function mod_events_get_event($event_id) {
	require_once __DIR__.'/../zzbrick_request_get/events.inc.php';

	$event = mod_events_get_events([$event_id => ['event_id' => $event_id]]);
	$event = reset($event);
	return $event;
}
