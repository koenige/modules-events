<?php 

/**
 * events module
 * form for projects
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023-2025 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Projects';
$zz['table'] = '/*_PREFIX_*/events';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'event_id';
$zz['fields'][1]['type'] = 'id';
$zz['fields'][1]['import_id_value'] = true;

$zz['fields'][12]['title_tab'] = 'Abbr.';
$zz['fields'][12]['title'] = 'Abbreviation';
$zz['fields'][12]['field_name'] = 'event_abbr';
$zz['fields'][12]['hide_in_list_if_empty'] = true;

$zz['fields'][6]['title'] = 'Project';
$zz['fields'][6]['field_name'] = 'event';
$zz['fields'][6]['type'] = 'text';
$zz['fields'][6]['list_prefix'] = '<strong>';
$zz['fields'][6]['list_suffix'] = '</strong>';
$zz['fields'][6]['if'][1]['list_prefix'] = '<del>';
$zz['fields'][6]['if'][1]['list_suffix'] = '</del> '.wrap_text('(cancelled)');
$zz['fields'][6]['if'][2]['list_prefix'] = '<del>';
$zz['fields'][6]['if'][2]['list_suffix'] = '</del>';
$zz['fields'][6]['size'] = 48;
$zz['fields'][6]['class'] = 'block480a';
$zz['fields'][6]['link'] = [
	'area' => 'events_project',
	'fields' => ['identifier']
];
$zz['fields'][6]['typo_cleanup'] = true;
$zz['fields'][6]['typo_remove_double_spaces'] = true;

$zz['fields'][4]['title'] = 'Begin';
$zz['fields'][4]['title_append'] = 'Period';
$zz['fields'][4]['title_tab'] = 'Period';
$zz['fields'][4]['field_name'] = 'date_begin';
$zz['fields'][4]['type'] = 'date';
$zz['fields'][4]['list_append_next'] = true;
$zz['fields'][4]['append_next'] = true;
$zz['fields'][4]['class'] = 'block480a number';

$zz['fields'][5]['list_prefix'] = '–';
$zz['fields'][5]['prefix'] = ' – ';
$zz['fields'][5]['title'] = 'End';
$zz['fields'][5]['field_name'] = 'date_end';
$zz['fields'][5]['type'] = 'date';
$zz['fields'][5]['validate']['>='] = ['date_begin'];
$zz['fields'][5]['validate_msg']['>='] = wrap_text('A project can only end after the start.');

$zz['fields'][8]['field_name'] = 'abstract';
$zz['fields'][8]['type'] = 'memo';
$zz['fields'][8]['rows'] = 8;
$zz['fields'][8]['format'] = 'markdown';
$zz['fields'][8]['typo_cleanup'] = true;
$zz['fields'][8]['list_append_next'] = true;
$zz['fields'][8]['separator'] = true;

// events_contacts 30 … 40
$zz['fields'][30] = [];
$zz['fields'][31] = [];
$zz['fields'][32] = [];
$zz['fields'][33] = [];
$zz['fields'][34] = [];
$zz['fields'][35] = [];
$zz['fields'][36] = [];
$zz['fields'][37] = [];
$zz['fields'][38] = [];
$zz['fields'][39] = [];

if (in_array('contacts', wrap_setting('modules'))) {
	$values['roles_restrict_to'] = 'projects';
	mf_default_categories_restrict($values, 'roles');

	$no = 30;
	foreach ($values['roles'] as $role)
		mf_contacts_contacts_subtable($zz, 'events', $role, $no++);
	$last_contact_no = $no - 1;
}

$zz['fields'][26]['title'] = 'Category';
$zz['fields'][26]['field_name'] = 'event_category_id';
$zz['fields'][26]['type'] = 'select';
$zz['fields'][26]['sql'] = 'SELECT category_id, category, description, main_category_id
	FROM /*_PREFIX_*/categories
	ORDER BY sequence, category';
$zz['fields'][26]['display_field'] = 'category';
$zz['fields'][26]['character_set'] = 'utf8';
$zz['fields'][26]['show_hierarchy'] = 'main_category_id';
$zz['fields'][26]['show_hierarchy_subtree'] = wrap_category_id('event');
$zz['fields'][26]['hide_in_list'] = true;
$zz['fields'][26]['hide_in_form'] = true;
$zz['fields'][26]['add_details'] = sprintf('categories?filter[maincategory]=%d', wrap_category_id('event'));
$zz['fields'][26]['type'] = 'hidden';
$zz['fields'][26]['type_detail'] = 'select';
$zz['fields'][26]['value'] = wrap_category_id('event/project');

// events_categories 50 … 59
$zz['fields'][50] = [];
$zz['fields'][51] = [];
$zz['fields'][52] = [];
$zz['fields'][53] = [];
$zz['fields'][54] = [];
$zz['fields'][55] = [];
$zz['fields'][56] = [];
$zz['fields'][57] = [];
$zz['fields'][58] = [];
$zz['fields'][59] = [];

mf_default_categories_subtable($zz, 'events', 'projects', 50);
if ($zz['fields'][50]) {
	$zz['fields'][50]['separator_before'] = true;
	if (isset($last_contact_no)) {
		while ($last_contact_no >= 30) {
			if (empty($zz['fields'][$last_contact_no]['hide_in_list'])) {
				$zz['fields'][$last_contact_no]['unless']['export_mode']['list_append_next'] = true;
				break;
			}
			$last_contact_no--;
		}
	}
}

// author
$zz['fields'][11] = [];

$zz['fields'][80] = zzform_include('events-media');
$zz['fields'][80]['title'] = 'Media';
$zz['fields'][80]['type'] = 'subtable';
$zz['fields'][80]['min_records'] = 1;
$zz['fields'][80]['max_records'] = wrap_setting('events_media_per_event');
$zz['fields'][80]['form_display'] = 'horizontal';
$zz['fields'][80]['sql'] .= ' ORDER BY sequence';
$zz['fields'][80]['fields'][2]['type'] = 'foreign_key';
$zz['fields'][80]['class'] = 'hidden480';
$zz['fields'][80]['hide_in_list_if_empty'] = true;
if ($zz['fields'][51])
	$zz['fields'][80]['separator_before'] = true;

if (wrap_setting('events_projects_links')) {
	$zz['fields'][16] = zzform_include('eventdetails');
	$zz['fields'][16]['title'] = 'Links';
	$zz['fields'][16]['type'] = 'subtable';
	$zz['fields'][16]['hide_in_list'] = true;
	$zz['fields'][16]['min_records'] = 1;
	$zz['fields'][16]['form_display'] = 'vertical';
	$zz['fields'][16]['fields'][2]['type'] = 'foreign_key';
	$zz['fields'][16]['fields'][6]['hide_in_form'] = true;
}

$zz['fields'][14]['title'] = 'Description';
$zz['fields'][14]['title_desc'] = '(optional)<br>';
$zz['fields'][14]['field_name'] = 'description';
$zz['fields'][14]['type'] = 'memo';
$zz['fields'][14]['hide_in_list'] = true;
$zz['fields'][14]['rows'] = 20;
$zz['fields'][14]['format'] = 'markdown';
$zz['fields'][14]['typo_cleanup'] = true;
$zz['fields'][14]['separator'] = true;


$zz['fields'][10]['title_tab'] = 'Web';
$zz['fields'][10]['field_name'] = 'published';
$zz['fields'][10]['type'] = 'select';
$zz['fields'][10]['enum'] = ['yes', 'no'];
$zz['fields'][10]['default'] = 'yes';
$zz['fields'][10]['class'] = 'hidden480';
$zz['fields'][10]['hide_in_list'] = true;

if (wrap_setting('events_show_in_news')) {
	$zz['fields'][23]['title_tab'] = 'N?';
	$zz['fields'][23]['title'] = 'News?';
	$zz['fields'][23]['explanation'] = 'Should the event appear in the news?';
	$zz['fields'][23]['field_name'] = 'show_in_news';
	$zz['fields'][23]['type'] = 'select';
	$zz['fields'][23]['enum'] = ['yes', 'no'];
	$zz['fields'][23]['default'] = 'no';
	$zz['fields'][23]['class'] = 'hidden480';
}

$zz['fields'][25]['title_tab'] = 'Seq.';
$zz['fields'][25]['field_name'] = 'sequence';
$zz['fields'][25]['type'] = 'number';
$zz['fields'][25]['auto_value'] = 'increment';
$zz['fields'][25]['hide_in_list'] = true;
$zz['fields'][25]['hide_in_list_if_empty'] = true;
$zz['fields'][25]['hide_in_form'] = true;
$zz['fields'][25]['dont_copy'] = true;
$zz['fields'][25]['explanation'] = 'Sorting if no time was specified.';


$zz['fields'][9]['title'] = 'Main Project';
$zz['fields'][9]['field_name'] = 'main_event_id';
$zz['fields'][9]['type'] = 'select';
$zz['fields'][9]['sql'] = 'SELECT event_id, event, main_event_id, identifier
	FROM events
	ORDER BY identifier';
$zz['fields'][9]['hide_in_list'] = true;
$zz['fields'][9]['show_hierarchy'] = 'main_event_id';
$zz['fields'][9]['show_hierarchy_same_table'] = true;

$zz['fields'][2]['field_name'] = 'identifier';
$zz['fields'][2]['type'] = 'identifier';
$zz['fields'][2]['fields'] = [
	'event_abbr', 'event', 'identifier'
];
$zz['fields'][2]['identifier']['ignore_this_if']['event'] = 'event_abbr';
$zz['fields'][2]['identifier']['exists'] = '-';
$zz['fields'][2]['hide_in_list'] = true;

$zz['fields'][21]['field_name'] = 'created';
$zz['fields'][21]['type'] = 'hidden';
$zz['fields'][21]['type_detail'] = 'datetime';
$zz['fields'][21]['default'] = date('Y-m-d H:i:s');
$zz['fields'][21]['hide_in_list'] = true;
$zz['fields'][21]['dont_copy'] = true;

$zz['fields'][22] = []; // website_id

$zz['fields'][99]['field_name'] = 'last_update';
$zz['fields'][99]['type'] = 'timestamp';
$zz['fields'][99]['hide_in_list'] = true;

$zz['sql'] = 'SELECT DISTINCT /*_PREFIX_*/events.*
	, DATE_FORMAT(time_begin, "%H:%i") AS time_begin
	, DATE_FORMAT(time_end, "%H:%i") AS time_end
	, /*_PREFIX_*/categories.category
	FROM /*_PREFIX_*/events
	LEFT JOIN /*_PREFIX_*/events_categories USING (event_id)
	LEFT JOIN /*_PREFIX_*/events_contacts USING (event_id)
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/events.event_category_id = /*_PREFIX_*/categories.category_id
	WHERE /*_PREFIX_*/events.event_category_id = /*_ID categories event/project _*/
';

$zz['sqlorder'] = ' ORDER BY date_begin DESC, IFNULL(time_begin, time_end) DESC, sequence DESC, identifier DESC';

$zz['conditions'][1]['scope'] = 'record';
$zz['conditions'][1]['where'] = 'takes_place = "no"';

$zz['conditions'][2]['scope'] = 'record';
$zz['conditions'][2]['where'] = 'published = "no"';

$zz['hooks']['before_insert'][] = 'mf_events_date_check';
$zz['hooks']['before_update'][] = 'mf_events_date_check';

$zz['filter'][1]['sql'] = 'SELECT DISTINCT YEAR(date_begin) AS year_idf
		, YEAR(date_begin) AS year
	FROM events
	ORDER BY YEAR(date_begin) DESC';
$zz['filter'][1]['title'] = wrap_text('Year');
$zz['filter'][1]['identifier'] = 'year';
$zz['filter'][1]['type'] = 'list';
$zz['filter'][1]['where'] = 'YEAR(date_begin)';

$zz['filter'][2]['sql'] = 'SELECT DISTINCT category_id
		, category
	FROM events_categories
	LEFT JOIN categories USING (category_id)
	ORDER BY category';
$zz['filter'][2]['title'] = wrap_text('Category');
$zz['filter'][2]['identifier'] = 'category';
$zz['filter'][2]['type'] = 'list';
$zz['filter'][2]['where'] = '/*_PREFIX_*/events_categories.category_id';

$zz['filter'][3]['sql'] = 'SELECT DISTINCT contact_id
		, IFNULL(contact_short, contact) AS contact
	FROM events_contacts
	LEFT JOIN contacts USING (contact_id)
	WHERE events_contacts.role_category_id = /*_ID categories roles/organiser _*/
	ORDER BY contact';
$zz['filter'][3]['title'] = wrap_text('Organiser');
$zz['filter'][3]['identifier'] = 'organiser';
$zz['filter'][3]['type'] = 'list';
$zz['filter'][3]['where'] = '/*_PREFIX_*/events_contacts.contact_id';

$zz['filter'][4]['title'] = wrap_text('Published');
$zz['filter'][4]['identifier'] = 'published';
$zz['filter'][4]['type'] = 'list';
$zz['filter'][4]['where'] = 'published';
$zz['filter'][4]['selection']['yes'] = wrap_text('yes');
$zz['filter'][4]['selection']['no'] = wrap_text('no');

$zz['record']['copy'] = true;

if (wrap_setting('multiple_websites')) {
	$zz['fields'][22]['field_name'] = 'website_id';
	$zz['fields'][22]['type'] = 'write_once';
	$zz['fields'][22]['type_detail'] = 'select';
	$zz['fields'][22]['sql'] = 'SELECT website_id, domain
		FROM /*_PREFIX_*/websites
		ORDER BY domain';
	$zz['fields'][22]['default'] = wrap_setting('website_id_default');
	$zz['fields'][22]['display_field'] = 'domain';
	$zz['fields'][22]['exclude_from_search'] = true;
	$zz['fields'][22]['if']['where']['hide_in_list'] = true;
	if (!empty($_GET['filter']['website'])) {
		$zz['fields'][22]['hide_in_list'] = true;
		$zz['fields'][22]['hide_in_form'] = true;
		$zz['fields'][22]['type'] = 'hidden';
		$zz['fields'][22]['value'] = $_GET['filter']['website'];
	}

	$zz['sql'] = 'SELECT DISTINCT /*_PREFIX_*/events.*
			, DATE_FORMAT(time_begin, "%H:%i") AS time_begin
			, DATE_FORMAT(time_end, "%H:%i") AS time_end
			, /*_PREFIX_*/categories.category
			, /*_PREFIX_*/websites.domain
		FROM /*_PREFIX_*/events
		LEFT JOIN /*_PREFIX_*/events_categories USING (event_id)
		LEFT JOIN /*_PREFIX_*/events_contacts USING (event_id)
		LEFT JOIN /*_PREFIX_*/categories
			ON /*_PREFIX_*/events.event_category_id = /*_PREFIX_*/categories.category_id
		LEFT JOIN /*_PREFIX_*/websites USING (website_id)
		WHERE /*_PREFIX_*/events.event_category_id = /*_ID categories event/project _*/
	';

	if (empty($zz['where']['website_id']) AND empty($_GET['where']['website_id'])) {
		$zz['filter'][1]['sql'] = 'SELECT website_id, domain
			FROM /*_PREFIX_*/websites
			WHERE website_id != 1
			ORDER BY domain';
		$zz['filter'][1]['title'] = 'Website';
		$zz['filter'][1]['identifier'] = 'website';
		$zz['filter'][1]['type'] = 'list';
		$zz['filter'][1]['field_name'] = 'website_id';
		$zz['filter'][1]['where'] = '/*_PREFIX_*/webpages.website_id';
	}

	$zz['subtitle']['website_id']['sql'] = $zz['fields'][22]['sql'];
	$zz['subtitle']['website_id']['var'] = ['domain'];
}
