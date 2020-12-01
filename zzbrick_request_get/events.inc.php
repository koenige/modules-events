<?php 

/**
 * events module
 * get events data per ID
 *
 * Part of »Zugzwang Project«
 * http://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * get event data per ID, pre-sorted
 * existing data is appended to event data
 *
 * @param array $data
 * @param string $id_field_name (optional, if key does not equal event_id)
 * @param string $lang_field_name (optional, if not current language shall be used)
 * @return array
 */
function mod_events_get_events($data, $id_field_name = '', $lang_field_name = '') {
	if (!$data) return $data;

	$ids = wrap_data_ids($data, $id_field_name);
	$langs = wrap_data_langs($data, $id_field_name);

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
			, CONCAT(CASE DAYOFWEEK(date_begin) WHEN 1 THEN "%s"
				WHEN 2 THEN "%s"
				WHEN 3 THEN "%s"
				WHEN 4 THEN "%s"
				WHEN 5 THEN "%s"
				WHEN 6 THEN "%s"
				WHEN 7 THEN "%s" END) AS weekday_begin
			, CONCAT(CASE DAYOFWEEK(date_end) WHEN 1 THEN "%s"
				WHEN 2 THEN "%s"
				WHEN 3 THEN "%s"
				WHEN 4 THEN "%s"
				WHEN 5 THEN "%s"
				WHEN 6 THEN "%s"
				WHEN 7 THEN "%s" END) AS weekday_end
			, IF(events.published = "yes", 1, NULL) AS published
			, timezone
			, main_event_id
			, category_id, category, hashtag
		FROM events
		LEFT JOIN categories
			ON events.event_category_id = categories.category_id
		WHERE events.event_id IN (%s)
		ORDER BY FIELD(events.event_id, %s)';
	$sql = sprintf($sql
		, wrap_text('Sun'), wrap_text('Mon'), wrap_text('Tue'), wrap_text('Wed')
		, wrap_text('Thu'), wrap_text('Fri'), wrap_text('Sat')
		, wrap_text('Sun'), wrap_text('Mon'), wrap_text('Tue'), wrap_text('Wed')
		, wrap_text('Thu'), wrap_text('Fri'), wrap_text('Sat')
		, implode(',', $ids), implode(',', $ids)
	);
	// @todo get correct translation for weekdays
	$eventdata = wrap_db_fetch($sql, 'event_id');
	foreach ($langs as $lang) {
		$events[$lang] = wrap_translate($eventdata, 'events', '', true, $lang);
		$events[$lang] = wrap_translate($events[$lang], 'categories', 'event_id', true, $lang);
	}

	// media
	$events = wrap_data_media($events, $ids, $langs, 'events', 'event');

	// categories
	$sql = 'SELECT event_category_id, event_id, category_id, category
		FROM events_categories
		LEFT JOIN categories USING (category_id)
		WHERE event_id = %d
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
		WHERE event_id IN (%d)
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

/**
 * get list of IDs from data
 * either data is indexed by ID or there is a separate ID field name
 *
 * @param array $data
 * @param string $id_field_name
 * @return array
 */
function wrap_data_ids($data, $id_field_name = '') {
	if (!$id_field_name) return array_keys($data);

	foreach ($data as $id => $line) {
		$ids[$id] = $line[$id_field_name];
	}
	return $ids;
}

/**
 * get list of language codes from data
 * either use standard language code from settings
 * or use a separate language field name
 *
 * @param array $data
 * @param string $lang_field_name
 * @return array
 */
function wrap_data_langs($data, $lang_field_name = '') {
	global $zz_setting;
	if (!$lang_field_name) return [$zz_setting['lang']];

	foreach ($data as $id => $line) {
		$langs[$line[$lang_field_name]] = $line[$lang_field_name];
	}
	return $langs;
}	

/**
 * get media for data
 *
 * @param array $data
 * @param array $ids
 * @param array $langs
 * @param string $table
 * @param string $id_field
 * @return array
 */
function wrap_data_media($data, $ids, $langs, $table, $id_field) {
	$mediadata = wrap_get_media(array_unique($ids), $table, $id_field);
	foreach ($langs as $lang) {
		$media[$lang] = wrap_translate($mediadata, 'media', 'medium_id', true, $lang);
	}
	foreach ($media as $lang => $media_per_lang) {
		foreach ($media_per_lang as $line_id => $line_media) {
			foreach ($langs as $lang) {
				$data[$lang][$line_id] += $line_media;
			}
		}
	}
	return $data;
}

/**
 * merge language specific data to existing $data array
 *
 * @param array $data
 * @param array $new_data
 * @param string $id_field_name
 * @param string $lang_field_name
 * @return array
 */
function wrap_data_merge($data, $new_data, $id_field_name = '', $lang_field_name = '') {
	global $zz_setting;

	foreach ($data as $id => $line) {
		if ($lang_field_name)
			$lang = $line[$lang_field_name];
		else
			$lang = $zz_setting['lang'];
		if ($id_field_name)
			$data[$id] = array_merge($new_data[$lang][$line[$id_field_name]], $line);
		else
			$data[$id] = array_merge($new_data[$lang][$id], $line);
	}
	return $data;
}
