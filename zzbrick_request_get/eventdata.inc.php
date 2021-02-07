<?php 

/**
 * events module
 * get events data per ID
 *
 * Part of »Zugzwang Project«
 * http://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020-2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * get event data per ID, pre-sorted
 * existing data is appended to event data
 *
 * @param array $data
 * @param array $settings
 * @param string $id_field_name (optional, if key does not equal event_id)
 * @param string $lang_field_name (optional, if not current language shall be used)
 * @return array
 */
function mod_events_get_eventdata($data, $settings = [], $id_field_name = '', $lang_field_name = '') {
	if (!$data) return $data;
	global $zz_setting;
	require_once $zz_setting['core'].'/data.inc.php';

	$ids = wrap_data_ids($data, $id_field_name);
	$langs = wrap_data_langs($data, $lang_field_name);

	$sql = 'SELECT event_id, identifier
			, event, abstract, events.description, date_begin, date_end
			, IF(date_begin >= CURDATE(), registration, NULL) AS registration
			, direct_link
			, CONCAT(date_begin, IFNULL(CONCAT("/", date_end), "")) AS duration
			, TIME_FORMAT(time_begin, "%%H.%%i") AS time_begin
			, TIME_FORMAT(time_end, "%%H.%%i") AS time_end
			, time_begin AS time_begin_iso
			, time_end AS time_end_iso
			, IF(takes_place = "yes", NULL, 1) AS cancelled
			, YEAR(IFNULL(date_begin, date_end)) AS year
			, DAYOFWEEK(date_begin) AS weekday_begin
			, DAYOFWEEK(date_end) AS weekday_end
			, IF(events.published = "yes", 1, NULL) AS published
			, timezone
			, main_event_id
			, category_id, category, hashtag
			, IF(CURDATE() > IFNULL(date_end, date_begin), 1, NULL) AS past_event
		FROM events
		LEFT JOIN categories
			ON events.event_category_id = categories.category_id
		WHERE events.event_id IN (%s)
		ORDER BY FIELD(events.event_id, %s)';
	$sql = sprintf($sql
		, implode(',', $ids), implode(',', $ids)
	);
	$eventdata = wrap_db_fetch($sql, 'event_id');
	foreach ($langs as $lang) {
		$events[$lang] = wrap_translate($eventdata, 'events', '', true, $lang);
		$events[$lang] = wrap_translate($events[$lang], 'categories', 'event_id', true, $lang);
		$events[$lang] = wrap_weekdays($events[$lang], ['weekday_begin', 'weekday_end'], $lang);
		foreach (array_keys($events[$lang]) as $event_id) {
			$events[$lang][$event_id][$lang] = true;
		}
	}

	// media
	$events = wrap_data_media($events, $ids, $langs, 'events', 'event');

	// categories
	$sql = 'SELECT event_category_id, event_id, category_id, category
		FROM events_categories
		LEFT JOIN categories USING (category_id)
		WHERE event_id IN (%s)
		ORDER by categories.sequence, category';
	$sql = sprintf($sql, implode(',', $ids));
	$categorydata = wrap_db_fetch($sql, 'event_category_id');
	foreach ($langs as $lang) {
		$categories[$lang] = wrap_translate($categorydata, 'categories', 'category_id', true, $lang);
	}
	foreach ($categories as $lang => $categories_per_lang) {
		foreach ($categories_per_lang as $event_category_id => $category) {
			$events[$lang][$category['event_id']]['categories'][$event_category_id] = $category; 
		}
	}

	// places
	$sql = 'SELECT event_id, event_contact_id, contact, contact_id
			, categories.category_id, categories.category
			, SUBSTRING_INDEX(categories.path, "/", -1) AS path
			, IF(address != contact, address, "") AS address
			, postcode
			, IF(place != contact, place, "") AS place
			, country, country_id, contacts.description
			, (SELECT identification FROM contactdetails cd
				WHERE cd.contact_id = contacts.contact_id
				AND cd.provider_category_id = %d) AS website
			, SUBSTRING_INDEX(contact_categories.path, "/", -1) AS contact_path
			, contacts.parameters
		FROM events_contacts
		LEFT JOIN contacts USING (contact_id)
		LEFT JOIN categories
			ON events_contacts.role_category_id = categories.category_id
		LEFT JOIN addresses USING (contact_id)
		LEFT JOIN countries USING (country_id)
	    LEFT JOIN categories contact_categories
	    	ON contact_categories.category_id = contacts.contact_category_id
		WHERE event_id IN (%s)
		ORDER BY events_contacts.sequence, contact';
	$sql = sprintf($sql
		, wrap_category_id('provider/website')
		, implode(',', $ids)
	);
	$contactdata = wrap_db_fetch($sql, 'event_contact_id');
	foreach ($contactdata as $event_contact_id => $contact) {
		if (!$contact['parameters']) continue;
		parse_str($contact['parameters'], $parameters);
		$contactdata[$event_contact_id] += $parameters;
	}
	foreach ($langs as $lang) {
		$contacts[$lang] = wrap_translate($contactdata, 'contacts', 'contact_id', true, $lang);
		$contacts[$lang] = wrap_translate($contacts[$lang], 'countries', 'country_id', true, $lang);
		$contacts[$lang] = wrap_translate($contacts[$lang], 'categories', 'category_id', true, $lang);
	}
	foreach ($contacts as $lang => $contacts_per_lang) {
		foreach ($contacts_per_lang as $event_contact_id => $contact) {
			if (!empty($contact['show_direct_link']))
				$contact['direct_link'] = $events[$lang][$contact['event_id']]['direct_link'];
			$events[$lang][$contact['event_id']][$contact['path']][$event_contact_id] = $contact;
		}
	}

	$data = wrap_data_merge($data, $events, $id_field_name, $lang_field_name);
	return $data;
}	
