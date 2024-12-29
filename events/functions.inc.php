<?php

/**
 * events module
 * common functions
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * get event IDs that are sub events for a given event
 *
 * @param int $event_id
 * @return array
 */
function mf_events_subevents($event_id) {
	if (!wrap_setting('events_series_category')) {
		$sql = 'SELECT event_id
			FROM /*_PREFIX_*/events
			WHERE main_event_id = %d
			AND event_category_id = /*_ID categories event/event _*/';
	} else {
		// @deprecated
		$sql = 'SELECT events.event_id
			FROM /*_PREFIX_*/events
			LEFT JOIN /*_PREFIX_*/categories series
				ON /*_PREFIX_*/events.series_category_id = series.category_id
			LEFT JOIN /*_PREFIX_*/events main_events
				ON main_events.series_category_id = series.main_category_id
			AND IFNULL(main_events.event_year, YEAR(main_events.date_begin)) = IFNULL(/*_PREFIX_*/events.event_year, YEAR(/*_PREFIX_*/events.date_begin))
			WHERE main_events.event_id = %d';
	}
	$sql = sprintf($sql, $event_id);
	return wrap_db_fetch($sql, '_dummy_', 'single value');
}

/**
 * get a list of organisations which are linked to an event
 *
 * @param int $event_id
 * @param array $params
 * @return array
 */
function mf_events_event_organisations($event_id, $params = []) {
	$sql = 'SELECT contact_id
			, contact
			, SUBSTRING_INDEX(roles.path, "/", -1) AS role_identifier
			, role_category_id
			, roles.category AS role_category
			, SUBSTRING_INDEX(organisation_types.path, "/", -1) AS type
			, contacts.identifier
			, contacts_contacts.contact_id AS place_contact_id
			, contacts_contacts.role
			, contacts.description
		FROM events_contacts
		LEFT JOIN contacts USING (contact_id)
		LEFT JOIN categories organisation_types
			ON organisation_types.category_id = contacts.contact_category_id
		LEFT JOIN contacts_contacts USING (contact_id)
		LEFT JOIN categories roles
			ON roles.category_id = events_contacts.role_category_id
		WHERE event_id = %d
		AND (ISNULL(contacts_contacts.contact_id) OR contacts_contacts.published = "yes")
		ORDER BY organisation_types.sequence, organisation_types.path
	';
	$sql = sprintf($sql, $event_id);
	$organisations = wrap_db_fetch($sql, 'contact_id');
	$details = mf_contacts_contactdetails(array_keys($organisations));
	if (!empty($params['addresses']))
		$addresses = mf_contacts_addresses(array_keys($organisations));
	$data = [];
	foreach ($organisations as $contact_id => $org) {
		$org[$org['type']] = 1;
		$org += $details[$contact_id] ?? [];
		$org['addresses'] = $addresses[$contact_id] ?? [];
		$data[$org['role_category']]['role_category'] = $org['role_category'];
		$data[$org['role_category']]['type'] = $org['type'];
		$data[$org['role_category']][$org['type']] = 1;
		$data[$org['role_category']]['organisations'][] = $org;
		if ($org['place_contact_id']) $contact_ids[] = $org['place_contact_id'];
	}
	return array_values($data);
}

/**
 * get organiser contacts for an event
 *
 * @param int $event_id
 * @return array
 */
function mf_events_organisers($event_id) {
	$sql = 'SELECT contact_id, contact
		FROM events_contacts
		LEFT JOIN contacts USING (contact_id)
		WHERE event_id = %d
		AND role_category_id = /*_ID CATEGORIES roles/organiser _*/';
	$sql = sprintf($sql, $event_id);
	return wrap_db_fetch($sql, 'contact_id');
}
