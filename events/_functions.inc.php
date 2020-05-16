<?php 

/**
 * events module
 * Table definition for 'events'
 *
 * Part of »Zugzwang Project«
 * http://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * read organisers per event
 *
 * @param array $events, index on event_id
 * @return array $events with 'organiser'
 */
function mod_events_event_organisers($events) {
	$sql = 'SELECT contact_id, event_id, contact
			, IF(published = "yes", identifier, NULL) AS identifier
		FROM events_contacts
		LEFT JOIN contacts USING (contact_id)
		WHERE event_id IN (%s)
		AND role_category_id = %d';
	$sql = sprintf($sql,
		implode(',', array_keys($events)),
		wrap_category_id('roles/organiser')
	);
	$organisers = wrap_db_fetch($sql, ['event_id', 'contact_id']);
	foreach ($organisers as $event_id => $event_orgs) {
		$events[$event_id]['organisers'] = $event_orgs;
	}
	foreach ($events as $event_id => $event) {
		if (empty($event['main_event_id'])) continue;
		if (empty($events[$event['main_event_id']]['organisers'])) continue;
		$events[$event_id]['organisers'] = $events[$event['main_event_id']]['organisers'];
	}
	return $events;
}
