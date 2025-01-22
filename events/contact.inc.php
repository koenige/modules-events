<?php 

/**
 * events module
 * contact functions
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2024-2025 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mf_events_contact($data, $ids) {
	$sql = 'SELECT event_contact_id, contact_id, event_id, event, identifier
			, CONCAT(date_begin, IFNULL(CONCAT("/", date_end), "")) AS duration
			, category_id, category as role
			, event_category_id
	    FROM events_contacts
	    LEFT JOIN events USING (event_id)
	    LEFT JOIN categories
	    	ON events_contacts.role_category_id = categories.category_id
	    WHERE contact_id IN (%s)
	    ORDER BY IFNULL(date_begin, date_end) DESC, events_contacts.sequence';
	$sql = sprintf($sql, implode(',', $ids));
	$events = wrap_db_fetch($sql, 'event_id');
	$events = wrap_translate($events, 'events', 'event_id');
	$events = wrap_translate($events, 'contacts', 'contact_id');
	$events = wrap_translate($events, ['role' => 'categories.category'], 'category_id');

	foreach ($events as $event_contact_id => $event) {
		if (!array_key_exists('events', $data[$event['contact_id']])) {
			$data[$event['contact_id']]['events'] = [];
			$data[$event['contact_id']]['projects'] = [];
		}
		if ($event['event_category_id'] === wrap_category_id('event/project'))
			$data[$event['contact_id']]['projects'][$event['event_id']] = $event;
		else
			$data[$event['contact_id']]['events'][$event['event_id']] = $event;
	}
	
	$data['templates']['contact_5'][] = 'contact-events';
	return $data;
}
