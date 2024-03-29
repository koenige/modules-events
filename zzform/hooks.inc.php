<?php 

/**
 * events module
 * Database hooks for zzform
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020-2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * Event update: check dates
 * 
 * @param array $ops
 * @return array
 */
function mf_events_date_check($ops) {
	$changes = [];
	foreach ($ops['planned'] as $index => $table) {
		if ($table['table'] === 'events') {
			if (empty($ops['record_new'][$index]['date_end'])) continue;
			if ($ops['record_new'][$index]['date_end'] === $ops['record_new'][$index]['date_begin']) {
				$changes['record_replace'][$index]['date_end'] = '';
			}
		}
	}
	return $changes;
}
