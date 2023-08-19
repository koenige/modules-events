<?php 

/**
 * events module
 * get events data per ID
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020-2023 Gustaf Mossakowski
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
	require_once wrap_setting('core').'/data.inc.php';

	$ids = wrap_data_ids($data, $id_field_name);
	$langs = wrap_data_langs($data, $lang_field_name);

	$sql = 'SELECT event_id
			, IF(event_category_id = %d, identifier, NULL) AS identifier
			, identifier AS uid
			, event, abstract, events.description, date_begin, date_end
			, IF(date_begin >= CURDATE(), registration, NULL) AS registration
			, CONCAT(date_begin, IFNULL(CONCAT("/", date_end), "")) AS duration
			, TIME_FORMAT(time_begin, "%%H.%%i") AS time_begin
			, TIME_FORMAT(time_end, "%%H.%%i") AS time_end
			, time_begin AS time_begin_iso
			, time_end AS time_end_iso
			, IF(takes_place = "yes", NULL, 1) AS cancelled
			, YEAR(IFNULL(date_begin, date_end)) AS year
			, MONTH(IFNULL(date_begin, date_end)) AS month
			, DATE_FORMAT(IFNULL(date_begin, date_end), "%%Y-%%m-00") AS month_iso
			, WEEK(IFNULL(date_begin, date_end), 1) AS week
			, DAYOFWEEK(date_begin) AS weekday_begin
			, DAYOFWEEK(date_end) AS weekday_end
			, IF(events.published = "yes", 1, NULL) AS published
			, timezone
			, main_event_id
			, category_id, category, hashtag
			, IF(CURDATE() > IFNULL(date_end, date_begin), 1, NULL) AS past_event
			, IF(following = "yes", 1, NULL) AS following
		FROM events
		LEFT JOIN categories
			ON events.event_category_id = categories.category_id
		WHERE events.event_id IN (%s)
		ORDER BY FIELD(events.event_id, %s)';
	$sql = sprintf($sql
		, wrap_category_id('event/'.($settings['category'] ?? 'event'))
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
	$events = mod_events_get_eventdata_categories($events, $ids, $langs);

	// details (links)
	$events = mod_events_get_eventdata_details($events, $ids, $langs);

	// places
	$events = mod_events_get_eventdata_places($events, $ids, $langs);
	
	$data = wrap_data_merge($data, $events, $id_field_name, $lang_field_name);
	
	// mark equal fields
	$last_line = [];
	$fields = ['year', 'month_iso', 'duration', 'week'];
	foreach ($fields as $field)
		$last_line[$field] = NULL;
	foreach ($data as $event_id => $line) {
		if (!empty($line['location'])) {
			foreach ($line['location'] as $contact_id => $contact) {
				if (!$contact['country_id']) continue;
				if ($contact['country_id'].'' !== wrap_setting('own_country_id').'') continue;
				$data[$event_id]['location'][$contact_id]['own_country'] = true;
			}
		}
		foreach ($fields as $field) {
			if ($line[$field] !== $last_line[$field])
				$data[$event_id]['change_'.$field] = true;
			$last_line[$field] = $line[$field];
		}
	}
	return $data;
}	

/**
 * get categories per event
 *
 * @param array $events
 * @param array $ids
 * @param array $langs
 * @return array
 */
function mod_events_get_eventdata_categories($events, $ids, $langs) {
	$sql = 'SELECT event_category_id, event_id
			, categories.category_id, categories.category
			, categories.parameters
			, types.path AS type_path, types.category AS type_category
			, types.parameters AS type_parameters
			, property
		FROM events_categories
		LEFT JOIN categories USING (category_id)
		LEFT JOIN categories types
			ON events_categories.type_category_id = types.category_id
		WHERE event_id IN (%s)
		ORDER by categories.sequence, categories.category';
	$sql = sprintf($sql, implode(',', $ids));
	$data = wrap_db_fetch($sql, 'event_category_id');
	foreach ($langs as $lang) {
		$categories[$lang] = wrap_translate($data, 'categories', 'category_id', true, $lang);
	}
	foreach ($categories as $lang => $categories_per_lang) {
		foreach ($categories_per_lang as $event_category_id => $category) {
			if ($category['type_parameters'])
				parse_str($category['type_parameters'], $category['type_parameters']);
			$type_path = !empty($category['type_parameters']['alias'])
				? $category['type_parameters']['alias'] : $category['type_path'];
			if ($type_path === 'events') $type_path = 'categories';
			if ($category['parameters']) {
				parse_str($category['parameters'], $category['parameters']);
				$category += $category['parameters'];
			}
			$events[$lang][$category['event_id']][$type_path][$event_category_id] = $category; 
		}
	}
	return $events;
}

/**
 * get details per event
 *
 * @param array $events
 * @param array $ids
 * @param array $langs
 * @return array
 */
function mod_events_get_eventdata_details($events, $ids = [], $langs = []) {
	if (!$ids) $ids = array_keys($events);
	
	$sql = 'SELECT eventdetail_id, event_id
			, identification, label
			, categories.category_id, categories.category
			, categories.parameters
			, SUBSTRING_INDEX(categories.path, "/", -1) AS path
		FROM eventdetails
		LEFT JOIN categories
			ON eventdetails.detail_category_id = categories.category_id
		WHERE event_id IN (%s)
		AND active = "yes"
		ORDER by categories.sequence, identification';
	$sql = sprintf($sql, implode(',', $ids));
	$data = wrap_db_fetch($sql, 'eventdetail_id');
	foreach ($langs as $lang) {
		$details[$lang] = wrap_translate($data, 'categories', 'category_id', true, $lang);
	}
	if (!$langs) {
		$details[wrap_setting('lang')] = $data;
		$events[wrap_setting('lang')] = $events;
	}
	
	foreach ($details as $lang => $details_per_lang) {
		foreach ($details_per_lang as $eventdetail_id => $detail) {
			if ($detail['parameters']) {
				parse_str($detail['parameters'], $detail['parameters']);
				if (!empty($detail['parameters']['direct_link'])) {
					$events[$lang][$detail['event_id']]['direct_link'] = $detail['identification'];
					$events[$lang][$detail['event_id']]['direct_link_label'] = $detail['label'] ?? $detail['category'];
				}
			}
			$events[$lang][$detail['event_id']]['details'][$eventdetail_id] = $detail; 
		}
	}
	if (!$langs) {
		return $events[wrap_setting('lang')];
	}
	return $events;
}

/**
 * get places per event
 *
 * @param array $events
 * @param array $ids
 * @param array $langs
 * @return array
 */
function mod_events_get_eventdata_places($events, $ids, $langs) {
	$sql = 'SELECT event_id, event_contact_id, contact, contact_id
			, categories.category_id, categories.category
			, SUBSTRING_INDEX(categories.path, "/", -1) AS path
			, IF(address != contact, address, "") AS address
			, postcode
			, IF(place != contact, place, "") AS place
			, country, countries.country_id, contacts.description
			, (SELECT identification FROM contactdetails cd
				WHERE cd.contact_id = contacts.contact_id
				AND cd.provider_category_id = %d) AS website
			, SUBSTRING_INDEX(contact_categories.path, "/", -1) AS contact_path
			, contacts.parameters
			, categories.parameters AS category_parameters
			, latitude, longitude
		FROM events_contacts
		LEFT JOIN contacts USING (contact_id)
		LEFT JOIN categories
			ON events_contacts.role_category_id = categories.category_id
		LEFT JOIN addresses USING (contact_id)
		LEFT JOIN countries
			ON addresses.country_id = countries.country_id
	    LEFT JOIN categories contact_categories
	    	ON contact_categories.category_id = contacts.contact_category_id
		WHERE event_id IN (%s)
		ORDER BY events_contacts.sequence, contact';
	$sql = sprintf($sql
		, wrap_category_id('provider/website')
		, implode(',', $ids)
	);
	$data = wrap_db_fetch($sql, 'event_contact_id');
	foreach ($data as $event_contact_id => $contact) {
		if (!$contact['parameters']) continue;
		parse_str($contact['parameters'], $parameters);
		$data[$event_contact_id] += $parameters;
	}
	foreach ($langs as $lang) {
		$contacts[$lang] = wrap_translate($data, 'contacts', 'contact_id', true, $lang);
		$contacts[$lang] = wrap_translate($contacts[$lang], 'countries', 'country_id', true, $lang);
		$contacts[$lang] = wrap_translate($contacts[$lang], 'categories', 'category_id', true, $lang);
	}
	foreach ($contacts as $lang => $contacts_per_lang) {
		foreach ($contacts_per_lang as $event_contact_id => $contact) {
			$path = $contact['path'];
			if ($contact['category_parameters']) {
				parse_str($contact['category_parameters'], $params);
				if (!empty($params['alias'])) {
					if (!strpos($params['alias'], '/'))
						$path = $params['alias'];
					else
						$path = substr($params['alias'], strrpos($params['alias'], '/') + 1);
				}
			}
			if (!empty($contact['show_direct_link']))
				$contact['direct_link'] = $events[$lang][$contact['event_id']]['direct_link'] ?? '';
			$events[$lang][$contact['event_id']][$path][$event_contact_id] = $contact;
		}
	}
	return $events;
}
