<?php 

/**
 * events module
 * definition helper functions for forms with zzform
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2025 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * link events as subtable
 *
 * @param array $zz existing table definition so far
 * @param string $path path of main category used for categories
 * @param int $no no of field to start with
 */
function mf_events_events_subtable(&$zz, $path, $no) {
	$sql = 'SELECT category_id, category, parameters
		FROM categories
		WHERE main_category_id = /*_ID categories event _*/
		AND parameters LIKE "%%&%s=1%%"';
	$sql = sprintf($sql, $path);
	$categories = wrap_db_fetch($sql, 'category_id');
	if (!$categories) return;
	$categories = wrap_translate($categories, 'categories');

	foreach ($categories as $category) {
		parse_str($category['parameters'], $category['parameters']);
		$zz['fields'][$no]['title'] = $category['category'];
		$zz['fields'][$no]['type'] = 'subtable';
		$zz['fields'][$no]['table_name'] = 'events_'.$category['category_id'];
		$zz['fields'][$no]['table'] = 'events';
		$zz['fields'][$no]['form_display'] = 'lines';
		$zz['fields'][$no]['min_records'] = 1;
		$zz['fields'][$no]['max_records'] = 1;
		$zz['fields'][$no]['class'] = 'number';
		$zz['fields'][$no]['foreign_key_field_name'] = 'main_event_id';
		$zz['fields'][$no]['sql'] = sprintf('SELECT events.*
			FROM events
			LEFT JOIN categories
				ON events.event_category_id = categories.category_id
			WHERE category_id = %d', $category['category_id']);
		
		$zz['fields'][$no]['fields'][1]['title'] = 'ID';
		$zz['fields'][$no]['fields'][1]['field_name'] = 'event_id';
		$zz['fields'][$no]['fields'][1]['type'] = 'id';
		
		$zz['fields'][$no]['fields'][59]['title'] = 'Main Event';
		$zz['fields'][$no]['fields'][59]['field_name'] = 'main_event_id';
		$zz['fields'][$no]['fields'][59]['type'] = 'foreign_key';
		$zz['fields'][$no]['fields'][59]['type_detail'] = 'select';
		$zz['fields'][$no]['fields'][59]['sql'] = 'SELECT event_id, event, main_event_id, identifier
			FROM events
			ORDER BY identifier';
		$zz['fields'][$no]['fields'][59]['hide_in_list'] = true;
		
		$zz['fields'][$no]['fields'][4]['title'] = 'Begin';
		$zz['fields'][$no]['fields'][4]['title_append'] = 'Date';
		$zz['fields'][$no]['fields'][4]['title_tab'] = 'Date';
		$zz['fields'][$no]['fields'][4]['field_name'] = 'date_begin';
		$zz['fields'][$no]['fields'][4]['type'] = 'date';
		
		if (empty($category['parameters']['hide_date_end'])) {
			$zz['fields'][$no]['fields'][4]['append_next'] = true;
	
			$zz['fields'][$no]['fields'][5]['list_prefix'] = '–';
			$zz['fields'][$no]['fields'][5]['prefix'] = ' – ';
			$zz['fields'][$no]['fields'][5]['title'] = 'End';
			$zz['fields'][$no]['fields'][5]['field_name'] = 'date_end';
			$zz['fields'][$no]['fields'][5]['type'] = 'date';
		}
	
		$zz['fields'][$no]['fields'][6]['field_name'] = 'event';
		$zz['fields'][$no]['fields'][6]['type'] = 'hidden';
		$zz['fields'][$no]['fields'][6]['hide_in_form'] = true;
		$zz['fields'][$no]['fields'][6]['value'] = $category['wrap_source_content']['category'] ?? $category['category'];
		$zz['fields'][$no]['fields'][6]['def_val_ignore'] = true;
		
		$zz['fields'][$no]['fields'][60]['title'] = 'Category';
		$zz['fields'][$no]['fields'][60]['field_name'] = 'event_category_id';
		$zz['fields'][$no]['fields'][60]['type'] = 'hidden';
		$zz['fields'][$no]['fields'][60]['type_detail'] = 'select';
		$zz['fields'][$no]['fields'][60]['sql'] = 'SELECT category_id, category
			FROM /*_PREFIX_*/categories
			WHERE main_category_id = /*_ID categories event _*/';
		$zz['fields'][$no]['fields'][60]['value'] = $category['category_id'];
		$zz['fields'][$no]['fields'][60]['hide_in_form'] = true;
		$zz['fields'][$no]['fields'][60]['def_val_ignore'] = true;
		$zz['fields'][$no]['fields'][60]['display_field'] = 'category';
		$zz['fields'][$no]['fields'][60]['character_set'] = 'utf8';
		
		$zz['fields'][$no]['fields'][2]['field_name'] = 'identifier';
		$zz['fields'][$no]['fields'][2]['type'] = 'identifier';
		$zz['fields'][$no]['fields'][2]['fields'] = [
			'main_event_id[identifier]', 'event'
		];
		$zz['fields'][$no]['fields'][2]['identifier']['concat'] = '/';
		$zz['fields'][$no]['fields'][2]['identifier']['exists'] = '-';
		$zz['fields'][$no]['fields'][2]['hide_in_form'] = true;
		
		$zz['fields'][$no]['fields'][21]['field_name'] = 'created';
		$zz['fields'][$no]['fields'][21]['type'] = 'hidden';
		$zz['fields'][$no]['fields'][21]['type_detail'] = 'date';
		$zz['fields'][$no]['fields'][21]['default'] = date('Y-m-d H:i:s');
		$zz['fields'][$no]['fields'][21]['hide_in_form'] = true;
		$zz['fields'][$no]['fields'][21]['dont_copy'] = true;
		$zz['fields'][$no]['fields'][21]['def_val_ignore'] = true;
		
		$zz['fields'][$no]['subselect']['sql'] = 'SELECT main_event_id
			, CONCAT(IFNULL(date_begin, ""), "/", IFNULL(date_end, "")) AS duration
			FROM events';
		$zz['fields'][$no]['subselect']['prefix'] = '<p><em>';
		$zz['fields'][$no]['subselect']['suffix'] = '</em></p>';
		$zz['fields'][$no]['subselect']['list_field_format'] = 'wrap_date';
		$no++;
	}
}
