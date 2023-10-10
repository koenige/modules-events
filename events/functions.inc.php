<?php

/**
 * events module
 * common functions
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * get a list of organisations which are linked to an event
 *
 * @param int $event_id
 * @return array
 */
function mf_events_event_organisations($event_id) {
	$sql = 'SELECT contact_id
			, contact
			, SUBSTRING_INDEX(roles.path, "/", -1) AS role_identifier
			, roles.category AS role
			, SUBSTRING_INDEX(organisation_types.path, "/", -1) AS type
			, contacts.identifier
			, contacts_contacts.contact_id AS place_contact_id
			, contacts_contacts.role
		FROM events_contacts
		LEFT JOIN contacts USING (contact_id)
		LEFT JOIN categories organisation_types
			ON organisation_types.category_id = contacts.contact_category_id
		LEFT JOIN contacts_contacts USING (contact_id)
		LEFT JOIN categories roles
			ON roles.category_id = events_contacts.role_category_id
		WHERE event_id = %d
		AND (ISNULL(contacts_contacts.contact_id) OR contacts_contacts.published = "yes")
	';
	$sql = sprintf($sql, $event_id);
	$organisations = wrap_db_fetch($sql, 'contact_id');
	$details = mf_contacts_contactdetails(array_keys($organisations));
	$data = [];
	foreach ($organisations as $contact_id => $org) {
		$org[$org['type']] = 1;
		$org += $details[$contact_id] ?? [];
		$data[$org['role']]['role'] = $org['role'];
		$data[$org['role']]['organisations'][] = $org;
		if ($org['place_contact_id']) $contact_ids[] = $org['place_contact_id'];
	}
	return array_values($data);
}
