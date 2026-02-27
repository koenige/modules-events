<?php 

/**
 * events module
 * Database hooks for zzform
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020-2021, 2026 Gustaf Mossakowski
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

/**
 * update url_placeholders with event years after event change
 *
 * @param array $ops
 * @return void
 */
function mf_events_url_placeholder_years($ops) {
	$events_changed = false;
	foreach ($ops['return'] as $index => $table) {
		if ($table['table'] !== 'events') continue;
		if ($table['action'] === 'nothing') continue;
		$events_changed = true;
		break;
	}
	if (!$events_changed) return;

	$offset = wrap_setting('events_url_placeholder_year_future_offset');

	$sql = 'SELECT DISTINCT YEAR(IFNULL(date_begin, date_end)) AS year
		FROM /*_PREFIX_*/events
		WHERE IFNULL(date_begin, date_end) IS NOT NULL
		ORDER BY year';
	$years = wrap_db_fetch($sql, '_dummy_', 'single value');
	if (!$years) return;

	$max_year = max(intval(max($years)), intval(date('Y'))) + $offset;
	$min_year = intval(min($years));
	$all_years = range($min_year, $max_year);

	$existing = wrap_setting('url_placeholders[year]');
	if ($existing == $all_years) return;

	$new_value = '['.implode(', ', $all_years).']';
	wrap_setting_write('url_placeholders[year]', $new_value);
}
