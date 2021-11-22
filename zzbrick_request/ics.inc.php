<?php 

/**
 * events module
 * iCal export (ICS-Format)
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2009, 2014-2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


use Kigkonsult\Icalcreator\Vcalendar;
use Kigkonsult\Icalcreator\Vtimezone;

/**
 * output ICS calendar
 *
 * @param array $params
 *		one parameter = project name: full calendar
 *		two parameters = event
 * @return array
 */
function mod_events_ics($params) {
	global $zz_setting;
	global $zz_conf;

	if (count($params) === 1) {
		if (substr($params[0], -4) !== '.ics') return false;
		$page['headers']['filename'] = $params[0];
		$params[0] = substr($params[0], 0, -4);
		switch ($params[0]) {
		case $zz_setting['site']:
			$where_condition = '';
			break;
		default:
			return false;
		}
	} elseif (count($params) === 2) {
		if (substr($params[1], -4) !== '.ics') return false;
		$page['headers']['filename'] = $params[0].' '.$params[1];
		$params[1] = substr($params[1], 0, -4);
		$where_condition = sprintf(
			' AND (main_events.identifier = "%d/%s" OR events.identifier = "%d/%s")',
			$params[0], wrap_db_escape($params[1]),
			$params[0], wrap_db_escape($params[1]));
	} else {
		return false;
	}

	/*
	Note that the "DTEND" property is
      set to July 9th, 2007, since the "DTEND" property specifies the
      non-inclusive end of the event.
	*/

	// @todo translate country name
	$sql = 'SELECT events.event_id
			, DATE_FORMAT(IFNULL(events.date_begin, events.date_end), "%%Y%%m%%d") AS date_begin
			, DATE_FORMAT(events.time_begin, "T%%H%%i%%s") AS time_begin
			, DATE_FORMAT(IFNULL(events.date_end, events.date_begin), "%%Y%%m%%d") AS date_end
			, DATE_FORMAT(events.time_end, "T%%H%%i%%s") AS time_end
			, DATE_FORMAT(DATE_SUB(CONCAT(IFNULL(events.date_end, events.date_begin), " ", IFNULL(events.time_end, "00:00:00")), INTERVAL 1 HOUR), "%%Y%%m%%dT%%H%%i%%s") AS date_end_minus_1h
			, DATE_FORMAT(events.date_begin, "%%Y") AS year
			, DATE_FORMAT(IFNULL(events.date_end, events.date_begin), "%%Y") AS end_year
			, DATE_FORMAT(IFNULL(DATE_ADD(events.date_end, INTERVAL 1 DAY), events.date_begin), "%%Y%%m%%d") AS dt_date_end
			, events.last_update AS timestamp
			, events.event AS title
			, CONCAT(IFNULL(events.abstract, ""), " ", IFNULL(events.description, "")) AS description
			, IFNULL(events.direct_link, IFNULL(main_events.identifier, events.identifier)) AS url
			, events.identifier
			, category, parameters as category_parameters
			, events.main_event_id
			, IF (events.takes_place = "yes", 1, NULL) AS takes_place
			, IFNULL((SELECT GROUP_CONCAT(CONCAT(
					contact, "\n", IFNULL(CONCAT(address, "\n"), ""), IFNULL(CONCAT(postcode, " "), ""), IF(place != contact, CONCAT(place, "\n"), ""), country
				) SEPARATOR ", ") FROM events_contacts	
				LEFT JOIN contacts USING (contact_id)
				LEFT JOIN addresses USING (contact_id)
				LEFT JOIN countries
					ON addresses.country_id = countries.country_id
				WHERE events_contacts.event_id = events.event_id
				AND events_contacts.role_category_id = %d), 
				(SELECT GROUP_CONCAT(CONCAT(
					contact, "\n", IFNULL(CONCAT(address, "\n"), ""), IFNULL(CONCAT(postcode, " "), ""), IF(place != contact, CONCAT(place, "\n"), ""), country
				) SEPARATOR ", ") FROM events_contacts	
				LEFT JOIN contacts USING (contact_id)
				LEFT JOIN addresses USING (contact_id)
				LEFT JOIN countries
					ON addresses.country_id = countries.country_id
				WHERE events_contacts.event_id = events.main_event_id
				AND events_contacts.role_category_id = %d)
			) AS places
		FROM events
		LEFT JOIN events main_events
			ON events.main_event_id = main_events.event_id
		LEFT JOIN events_categories
			ON events.event_id = events_categories.event_id
		LEFT JOIN categories USING (category_id)
		WHERE events.published = "yes"
		%s
		ORDER BY IFNULL(events.date_begin, events.date_end), events.time_begin, events.date_end, events.time_end, events.sequence
	';
	$sql = sprintf($sql
		, wrap_category_id('roles/location')
		, wrap_category_id('roles/location')
		, $where_condition
	);
	$events = wrap_db_fetch($sql, 'event_id');
	if (!$events) return false;

	$events = mf_events_event_organisers($events);

	require_once $zz_setting['lib'].'/icalcreator/autoload.php';

	$tz = $zz_setting['timezone'];
	$v = Vcalendar::factory([Vcalendar::UNIQUE_ID => $zz_setting['hostname']]);
	$v->setMethod(Vcalendar::PUBLISH);
	$v->setXprop(Vcalendar::X_WR_CALNAME, $zz_setting['events_ics_calname']);
	$v->setXprop(Vcalendar::X_WR_CALDESC, $zz_setting['events_ics_caldesc']);
	$v->setXprop(Vcalendar::X_WR_TIMEZONE, $tz);
	$v->setConfig(Vcalendar::LANGUAGE, $zz_setting['lang']);

	foreach ($events as $event) {
		if (empty($event['main_event_id']) AND count($events) > 1) {
			unset($event['hour']); // don't show start and end date if timetable is present
		}
		parse_str($event['category_parameters'], $properties);
		if (!empty($event['organisers'])) {
			$event['description'] .= "\r\n\r\n".wrap_text('Organiser').": ";
			$i = 0;
			foreach ($event['organisers'] as $organiser) {
				$event['description'] .= $organiser['contact'];
				$i++;
				if ($i < count($event['organisers'])) {
					$event['description'] .= ', ';
				}
			}
		}
		$e = $v->newVevent();
		if (!empty($properties['prefix_title']) AND $event['category']) {
			$event['title'] = $event['category'].': '.$event['title'];
		}
		if (!$event['takes_place']) {
			$event['title'] = wrap_text('CANCELLED').': '.$event['title'];
		}
		$e->setSummary($event['title']);
		$e->setCategories($event['category']);

		if ($event['date_begin'] AND $event['date_end'] AND $event['date_begin'] !== $event['date_end']) {
			// Event lasting several days, no entry with time!
			$event['time_begin'] = NULL;
			$event['time_end'] = NULL;
		}

		if (isset($event['time_begin'])) {
			$e->setDtstart(
				new DateTime($event['date_begin'].$event['time_begin'], new DateTimezone($tz))
			);
			if ($event['time_end']) {
				$e->setDtend(
					new DateTime($event['date_end'].$event['time_end'], new DateTimezone($tz))
				);
			} else {
				if (empty($properties['default_duration_minutes'])) {
					// no indication of how long the event lasts, let’s assume an hour.
					$properties['default_duration_minutes'] = 60;
				}
				$duration = $properties['default_duration_minutes'] / 60;
				$duration_hours = floor($duration);
				$duration_mins = ($duration - $duration_hours) * 60;
				$duration = 'PT';
				if ($duration_hours) $duration .= sprintf('%dH', $duration_hours);
				if ($duration_mins) $duration .= sprintf('%dM', $duration_mins);
				$e->setDuration($duration);
			}
		} elseif (isset($event['time_end'])) {
			$e->setDtstart(
				new DateTime($event['date_end_minus_1h'], new DateTimezone($tz))
			);
			$e->setDtend(
				new DateTime($event['date_end'].$event['time_end'], new DateTimezone($tz))
			);
		} else {
			if (substr($event['date_begin'], -4) === '0000') {
				$event['date_begin'] = substr($event['date_begin'], 0, 4).'0101';
			} elseif (substr($event['date_begin'], -2) === '00') {
				$event['date_begin'] = substr($event['date_begin'], 0, 6).'01';
			}
			if (substr($event['dt_date_end'], -4) === '0000') {
				$event['dt_date_end'] = (substr($event['dt_date_end'], 0, 4) + 1).'0101';
			} elseif (substr($event['dt_date_end'], -2) === '00') {
				$event['dt_date_end'] = (substr($event['dt_date_end'], 0, 6) + 1).'01';
			}
			$e->setDtstart(
				new DateTime($event['date_begin']), ['VALUE' => 'DATE']
			);
			$e->setDtend(
				new DateTime($event['dt_date_end']), ['VALUE' => 'DATE']
			);
		}
		$e->setDescription(strip_tags(markdown($event['description'])));
		$e->setLocation($event['places']);
		$e->setUid($event['identifier'].'@'.$zz_setting['site']);
		$timestamp = gmdate('Ymd His', strtotime($event['timestamp']));
		$e->setDtstamp(str_replace(' ', 'T', $timestamp).'Z');
	}

	$v->vtimezonePopulate();

	$page['text'] = $v->createCalendar();
	$page['content_type'] = 'ics';
	return $page;
}
