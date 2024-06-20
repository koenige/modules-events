<?php 

/**
 * events module
 * contact functions
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mf_events_contact($data, $ids) {
	$sql = 'SELECT contact_id, event_id, event, identifier
			, CONCAT(date_begin, IFNULL(CONCAT("/", date_end), "")) AS duration
			, category as role
	    FROM events_contacts
	    LEFT JOIN events USING (event_id)
	    LEFT JOIN categories
	    	ON events_contacts.role_category_id = categories.category_id
	    WHERE contact_id IN (%s)
	    ORDER BY IFNULL(date_begin, date_end) DESC, events_contacts.sequence';
	$sql = sprintf($sql, implode(',', $ids));
	$events_per_contact = wrap_db_fetch($sql, ['contact_id', 'event_id']);
	
	foreach ($events_per_contact as $contact_id => $events)
		$data[$contact_id]['events'] = $events;
	
	$data['templates']['contact_5'][] = 'contact-events';
	return $data;
}
