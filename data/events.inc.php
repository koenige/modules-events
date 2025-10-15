<?php 

/**
 * events module
 * get events data per ID
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020-2025 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * get event data per ID, pre-sorted
 * existing data is appended to event data
 *
 * @param array $ids
 * @param array $langs
 * @return array
 */
function mf_events_data($ids, $langs, $settings = []) {
	$sql = 'SELECT event_id
			, IF(event_category_id = /*_ID categories event/%s _*/, identifier, NULL) AS identifier
			, identifier AS uid
			, event, abstract, events.description
			, date_begin, date_end
			, STR_TO_DATE(REPLACE(date_begin, "-00", "-01"), "%%Y-%%m-%%d") AS date_begin_iso
			, CASE 
				WHEN date_end LIKE "%%-00" THEN LAST_DAY(STR_TO_DATE(REPLACE(date_end, "-00", "-01"), "%%Y-%%m-%%d"))
				WHEN date_end LIKE "%%-00-00" THEN LAST_DAY(STR_TO_DATE(REPLACE(date_end, "-00-00", "-01-01"), "%%Y-%%m-%%d"))
				ELSE STR_TO_DATE(date_end, "%%Y-%%m-%%d")
			END AS date_end_iso
			, IF(date_begin >= CURDATE(), registration, NULL) AS registration
			, CONCAT(IFNULL(date_begin, ""), IFNULL(CONCAT("/", date_end), "")) AS duration
			, TIME_FORMAT(time_begin, "%%H.%%i") AS time_begin
			, TIME_FORMAT(time_end, "%%H.%%i") AS time_end
			, time_begin AS time_begin_iso
			, time_end AS time_end_iso
			, IF(takes_place = "yes", NULL, 1) AS cancelled
			, IFNULL(event_year, YEAR(IFNULL(date_begin, date_end))) AS year
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
			, events.parameters
		FROM events
		LEFT JOIN categories
			ON events.event_category_id = categories.category_id
		WHERE events.event_id IN (%s)
		ORDER BY FIELD(events.event_id, %s)';
	$sql = sprintf($sql
		, $settings['category'] ?? 'event'
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
	$deleted = [];
	// media required?
	if (!empty($settings['category']) AND $settings['category'] === 'project' AND wrap_setting('events_project_needs_images')) {
		foreach ($events as $lang => $events_per_lang) {
			foreach ($events_per_lang as $event_id => $event) {
				if (!empty($event['images'])) continue;
				unset($events[$lang][$event_id]);
				$key = array_search($event_id, $ids);
				unset($ids[$key]);
				$deleted[] = $event_id;
			}
		}
	}
	if (!$ids) return ['deleted' => $deleted];
	
	// categories
	$events = mf_events_categories($events, $ids, $langs);

	// details (links)
	$events = mf_events_details($events, $ids, $langs);

	// contacts
	// @todo use wrap_data_package(), read from contacts module
	if (wrap_package('contacts'))
		$events = mf_events_contacts($events, $ids, $langs);
	
	return [$events, 'deleted' => $deleted];
}

/**
 * get further event data after merging results
 *
 * @param array $data
 * @param array $ids
 * @return array
 */
function mf_events_data_finalize($data, $ids) {
	$data = wrap_data_packages('events', $data, $ids);

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
function mf_events_categories($events, $ids, $langs) {
	$sql = 'SELECT event_category_id, event_id
			, categories.category_id, categories.category, categories.category_short
			, categories.parameters
			, categories.main_category_id
			, categories.path
			, main_categories.category AS main_category
			, main_categories.parameters AS main_parameters
			, main_categories.path AS main_path
			, SUBSTRING_INDEX(types.path, "/", -1) AS type_path
			, types.category AS type_category
			, types.parameters AS type_parameters
			, property
		FROM events_categories
		LEFT JOIN categories USING (category_id)
		LEFT JOIN categories types
			ON events_categories.type_category_id = types.category_id
		LEFT JOIN categories main_categories
			ON main_categories.category_id = categories.main_category_id
			AND categories.main_category_id != events_categories.type_category_id
		WHERE event_id IN (%s)
		AND (ISNULL(categories.parameters) OR categories.parameters NOT LIKE "%%&hide_category=1%%")
		ORDER by categories.sequence, categories.category';
	$sql = sprintf($sql, implode(',', $ids));
	$data = wrap_db_fetch($sql, 'event_category_id');
	foreach ($langs as $lang) {
		$categories[$lang] = wrap_translate($data, 'categories', 'category_id', true, $lang);
		$categories[$lang] = wrap_translate($categories[$lang], ['main_category' => 'categories.category'], 'main_category_id', true, $lang);
	}
	$parameter_types = ['parameters', 'main_parameters'];
	foreach ($categories as $lang => $categories_per_lang) {
		foreach ($categories_per_lang as $event_category_id => $category) {
			if ($category['type_parameters'])
				parse_str($category['type_parameters'], $category['type_parameters']);
			foreach ($parameter_types as $parameter_type) {
				if (!$category[$parameter_type]) continue;
				parse_str($category[$parameter_type], $category[$parameter_type]);
				$category += $category[$parameter_type];
				if ($path = mf_default_categories_menu_hierarchy($category[$parameter_type], $category['path']))
					$events[$lang][$category['event_id']]['menu_hierarchy'][] = $path;
			}
			if (!empty($category['type_parameters']['use_subtree'])) {
				$type_path = $category['main_parameters']['alias'] ?? $category['main_path'];
				if ($type_path AND $pos = strrpos($type_path, '/')) $type_path = substr($type_path, $pos + 1);
				$events[$lang][$category['event_id']][$type_path.'_category'] = $category['main_category'];
			} else {
				$type_path = $category['type_parameters']['alias'] ?? $category['type_path'];
			}
			if (in_array($type_path, ['events', 'projects'])) $type_path = 'categories';
			$c_path = $category['path'];
			if (str_starts_with($c_path, $category['main_path'].'/'))
				$c_path = substr($c_path, strlen($category['main_path']) + 1);
			$type_category_path = sprintf('%s_category_%s', $type_path, str_replace('/', '_', $c_path));
			$events[$lang][$category['event_id']][$type_path][$category['category_id']] = $category;
			$events[$lang][$category['event_id']][$type_category_path] = true;
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
function mf_events_details($events, $ids = [], $langs = []) {
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
					continue; // do not add the direct link to details
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
function mf_events_contacts($events, $ids, $langs) {
	$sql = 'SELECT event_id, event_contact_id, contact, contact_id, contacts.identifier
			, categories.category_id, categories.category
			, SUBSTRING_INDEX(categories.path, "/", -1) AS path
			, IF(address != contact, address, "") AS address
			, postcode
			, IF(place != contact, place, "") AS place
			, country, countries.country_id, contacts.description
			, (SELECT identification FROM contactdetails cd
				WHERE cd.contact_id = contacts.contact_id
				AND cd.provider_category_id = /*_ID categories provider/website _*/) AS website
			, SUBSTRING_INDEX(contact_categories.path, "/", -1) AS contact_path
			, contacts.parameters
			, categories.parameters AS category_parameters
			, latitude, longitude
			, role
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
		ORDER BY categories.sequence, categories.path, events_contacts.sequence, contact';
	$sql = sprintf($sql, implode(',', $ids));
	$data = wrap_db_fetch($sql, 'event_contact_id');
	$contact_ids = [];
	foreach ($data as $event_contact_id => $contact) {
		$contact_ids[] = $contact['contact_id'];
		$data[$event_contact_id][$contact['path']] = true;
		if (!$contact['parameters']) continue;
		parse_str($contact['parameters'], $parameters);
		$data[$event_contact_id] += $parameters;
	}
	foreach ($langs as $lang) {
		$contacts[$lang] = wrap_translate($data, 'contacts', 'contact_id', true, $lang);
		$contacts[$lang] = wrap_translate($contacts[$lang], 'countries', 'country_id', true, $lang);
		$contacts[$lang] = wrap_translate($contacts[$lang], 'categories', 'category_id', true, $lang);
	}
	if ($contact_ids)
		$contacts = wrap_data_media($contacts, $contact_ids, $langs, 'contacts', 'contact', true);
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
				if (!empty($params['events_own_event']) AND wrap_setting('own_contact_id')) {
					if ($contact['contact_id'] == wrap_setting('own_contact_id'))
						$events[$lang][$contact['event_id']]['own_event'] = true;
				}
			}
			if (!empty($contact['show_direct_link']))
				$contact['direct_link'] = $events[$lang][$contact['event_id']]['direct_link'] ?? '';
			if (!empty($contact['images']))
				$events[$lang][$contact['event_id']][$path.'_has_images'] = true;
			else
				$contact['images'] = [0 => []]; // dummy, do not show main image @todo change in zzbrick
			$events[$lang][$contact['event_id']]['contacts'][$contact['category_id']]['category'] = $contact['category'];
			$events[$lang][$contact['event_id']]['contacts'][$contact['category_id']][$contact['path']] = true;
			$events[$lang][$contact['event_id']]['contacts'][$contact['category_id']]['contacts'][] = $contact;
			if (!empty($contact['images']))
				$events[$lang][$contact['event_id']]['contacts'][$contact['category_id']]['has_images'] = true;
			// @deprecated
			$events[$lang][$contact['event_id']][$path][$event_contact_id] = $contact;
		}
	}
	// inherit contacts from main event, if no contacts are set
	foreach ($events as $lang => $lang_events) {
		foreach ($lang_events as $event_id => $event) {
			if (empty($event['main_event_id'])) continue;
			if (!empty($event['contacts'])) continue;
			if (empty($lang_events[$event['main_event_id']]['contacts'])) continue;
			$events[$lang][$event_id]['contacts'] = $lang_events[$event['main_event_id']]['contacts'];
		}
	}
	return $events;
}
