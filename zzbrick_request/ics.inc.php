<?php 

/**
 * events module
 * iCal export (ICS-Format)
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2009, 2014-2021, 2023 Gustaf Mossakowski
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
	if (count($params) === 1) {
		if (substr($params[0], -4) !== '.ics') return false;
		$page['headers']['filename'] = $params[0];
		$params[0] = substr($params[0], 0, -4);
		switch ($params[0]) {
		case wrap_setting('site'):
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

	$sql = 'SELECT events.event_id
			, DATE_FORMAT(IFNULL(events.date_begin, events.date_end), "%%Y%%m%%d") AS date_begin
			, DATE_FORMAT(events.time_begin, "T%%H%%i%%s") AS time_begin
			, DATE_FORMAT(IFNULL(events.date_end, events.date_begin), "%%Y%%m%%d") AS date_end
			, DATE_FORMAT(events.time_end, "T%%H%%i%%s") AS time_end
			, DATE_FORMAT(DATE_SUB(CONCAT(IFNULL(events.date_end, events.date_begin), " ", IFNULL(events.time_end, "00:00:00")), INTERVAL 1 HOUR), "%%Y%%m%%dT%%H%%i%%s") AS date_end_minus_1h
			, DATE_FORMAT(IFNULL(events.date_end, events.date_begin), "%%Y") AS end_year
			, DATE_FORMAT(IFNULL(DATE_ADD(events.date_end, INTERVAL 1 DAY), events.date_begin), "%%Y%%m%%d") AS dt_date_end
			, events.last_update AS timestamp
		FROM events
		LEFT JOIN events main_events
			ON events.main_event_id = main_events.event_id
		WHERE events.published = "yes"
		%s
		ORDER BY IFNULL(events.date_begin, events.date_end), events.time_begin, events.date_end, events.time_end, events.sequence
	';

	$sql = sprintf($sql, $where_condition);
	$events = wrap_db_fetch($sql, 'event_id');
	if (!$events) return false;
	require_once __DIR__.'/../zzbrick_request_get/eventdata.inc.php';
	$events = mod_events_get_eventdata($events);
	
	require_once wrap_setting('lib').'/icalcreator/autoload.php';

	$tz = wrap_setting('timezone');
	$v = Vcalendar::factory([Vcalendar::UNIQUE_ID => wrap_setting('hostname')]);
	$v->setUid(wrap_setting('hostname'));
	$v->setMethod(Vcalendar::PUBLISH);
	$v->setXprop(Vcalendar::X_WR_CALNAME, wrap_setting('events_ics_calname'));
	$v->setXprop(Vcalendar::X_WR_CALDESC, wrap_setting('events_ics_caldesc'));
	$v->setXprop(Vcalendar::X_WR_TIMEZONE, $tz);
	$v->setConfig(Vcalendar::LANGUAGE, wrap_setting('lang'));

	foreach ($events as $event) {
		$e = $v->newVevent();
		// inherit from main_event
		if (!empty($event['main_event_id'])) {
			$inherits = ['organiser', 'location'];
			foreach ($inherits as $inherit) {
				if (!empty($event[$inherit])) continue;
				if (empty($events[$event['main_event_id']][$inherit])) continue;
				$event[$inherit] = $events[$event['main_event_id']][$inherit];
			}
		}
		// hour
		if (empty($event['main_event_id']) AND count($events) > 1) {
			unset($event['hour']); // don't show start and end date if timetable is present
		}
		// description
		if (!empty($event['abstract']))
			$event['description'] = sprintf('%s %s', $event['abstract'], $event['description']);
		if (!empty($event['organiser'])) {
			$event['description'] .= "\r\n\r\n".wrap_text('Organiser').": ";
			$i = 0;
			foreach ($event['organiser'] as $organiser) {
				$event['description'] .= $organiser['contact'];
				$i++;
				if ($i < count($event['organiser'])) {
					$event['description'] .= ', ';
				}
			}
		}
		// categories
		$categories = [];
		$properties = [];
		if (!empty($event['categories'])) foreach ($event['categories'] as $category) {
			$categories[] = $category['category'];
			if (!empty($category['parameters'])) {
				parse_str($category['parameters'], $category_properties);
				$properties += $category_properties;
			}
		}
		$event['category'] = implode(',', $categories);
		
		if (!empty($properties['prefix_title']) AND $event['category']) {
			$event['event'] = $event['category'].': '.$event['event'];
		}
		if ($event['cancelled']) {
			$event['event'] = wrap_text('CANCELLED').': '.$event['event'];
		}
		$e->setSummary($event['event']);
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
		$e->setDescription(trim(strip_tags(markdown($event['description']))));
		if (!empty($event['location'])) {
			$locations = [];
			foreach ($event['location'] as $location) {
				$locations[] = $location['contact']
					.(!empty($location['address']) ? "\n".$location['address'] : '')
					.(($location['place'] AND $location['place'] !== $location['contact'])
						? "\n".(!empty($location['postcode']) ? $location['postcode']." " : '').$location['place']
						: ''
					)
					.(!empty($location['country']) ? "\n".$location['country'] : '');
			}
			$e->setLocation(implode(', ', $locations));
		}
		
		$e->setUid($event['uid'].'@'.wrap_setting('site'));
		$timestamp = gmdate('Ymd His', strtotime($event['timestamp']));
		$e->setDtstamp(str_replace(' ', 'T', $timestamp).'Z');
	}

	$v->vtimezonePopulate();

	$page['text'] = $v->createCalendar();
	$page['content_type'] = 'ics';
	return $page;
}
