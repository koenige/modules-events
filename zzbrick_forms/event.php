<?php 

/**
 * events module
 * Form for 'events'
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023-2024, 2026 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


if (empty($brick['data']['event_id'])) wrap_quit(404);

$zz = zzform_include('events', [], 'forms');
$zz['where']['event_id'] = $brick['data']['event_id'];
$zz['title'] = 'Event';

$zz['access'] = 'edit_only';
$zz['page']['referer'] = '../';

$zz['subtitle']['event_id']['sql'] = 'SELECT event_id, event
	, CONCAT(events.date_begin, IFNULL(CONCAT("/", events.date_end), "")) AS duration
	FROM events';
$zz['subtitle']['event_id']['var'] = ['event', 'duration'];
$zz['subtitle']['event_id']['format'][1] = 'wrap_date';
$zz['subtitle']['event_id']['link'] = '../';
$zz['subtitle']['event_id']['link_no_append'] = true;
