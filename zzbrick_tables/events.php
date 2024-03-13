<?php 

/**
 * events module
 * Table definition for 'events'
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2005-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Events';
$zz['table'] = '/*_PREFIX_*/events';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'event_id';
$zz['fields'][1]['type'] = 'id';
$zz['fields'][1]['import_id_value'] = true;

$zz['fields'][4]['title'] = 'Begin';
$zz['fields'][4]['title_append'] = 'Date';
$zz['fields'][4]['title_tab'] = 'Date';
$zz['fields'][4]['field_name'] = 'date_begin';
$zz['fields'][4]['type'] = 'date';
$zz['fields'][4]['default'] = wrap_date(date('Y-m-d', time()));
$zz['fields'][4]['explanation'] = 'If the exact date has not yet been set, the day and or month can be omitted here – then only the month is displayed! Leave the end empty if necessary.';
$zz['fields'][4]['list_append_next'] = true;
$zz['fields'][4]['append_next'] = true;
$zz['fields'][4]['class'] = 'block480a';

$zz['fields'][5]['list_prefix'] = '–';
$zz['fields'][5]['prefix'] = ' – ';
$zz['fields'][5]['title'] = 'End';
$zz['fields'][5]['field_name'] = 'date_end';
$zz['fields'][5]['type'] = 'date';
$zz['fields'][5]['list_append_next'] = true;
$zz['fields'][5]['validate']['>='] = ['date_begin'];
$zz['fields'][5]['validate_msg']['>='] = wrap_text('An event can only end after the start.');

$zz['fields'][54]['title'] = 'Time Begin';
$zz['fields'][54]['title_append'] = 'Time';
$zz['fields'][54]['field_name'] = 'time_begin';
$zz['fields'][54]['type'] = 'time';
$zz['fields'][54]['append_next'] = true;
$zz['fields'][54]['suffix'] = ' '.wrap_text('h');
$zz['fields'][54]['list_append_next'] = true;
$zz['fields'][54]['list_prefix'] = '<br>';
$zz['fields'][54]['list_suffix'] = ' '.wrap_text('h');

$zz['fields'][55]['title'] = 'Time End';
$zz['fields'][55]['field_name'] = 'time_end';
$zz['fields'][55]['type'] = 'time';
$zz['fields'][55]['prefix'] = ' – ';
$zz['fields'][55]['suffix'] = ' '.wrap_text('h');
$zz['fields'][55]['list_prefix'] = ' – ';
$zz['fields'][55]['list_suffix'] = ' '.wrap_text('h');

$zz['fields'][56]['title_tab'] = 'TZ';
$zz['fields'][56]['field_name'] = 'timezone';
$zz['fields'][56]['explanation'] = 'Format: -0100 or +0630 etc.';
$zz['fields'][56]['pattern'] = '^[+-][0-9]{4}$';
$zz['fields'][56]['hide_in_list_if_empty'] = true;
if (wrap_setting('events_timezone')) {
	$zz['fields'][56]['type'] = 'hidden';
	$zz['fields'][56]['value'] = wrap_setting('events_timezone');
	$zz['fields'][56]['hide_in_form'] = true;
	$zz['fields'][56]['hide_in_list'] = true;
} elseif (wrap_setting('events_timezone_default')) {
	$zz['fields'][56]['default'] = wrap_setting('events_timezone_default');
}

$zz['fields'][53]['title'] = 'Year';
$zz['fields'][53]['field_name'] = 'event_year';
$zz['fields'][53]['explanation'] = 'If different from the time of the event';
$zz['fields'][53]['hide_in_list'] = true;
$zz['fields'][53]['hide_in_form'] = true;
if (wrap_access('events_event_year') AND wrap_setting('events_event_year'))
	$zz['fields'][53]['hide_in_form'] = false;

$zz['fields'][6]['separator_before'] = true;
$zz['fields'][6]['field_name'] = 'event';
$zz['fields'][6]['type'] = 'text';
$zz['fields'][6]['list_prefix'] = '<strong>';
$zz['fields'][6]['list_suffix'] = '</strong><p>';
$zz['fields'][6]['if'][1]['list_prefix'] = '<del>';
$zz['fields'][6]['if'][1]['list_suffix'] = '</del> '.wrap_text('(cancelled)').'<p>';
$zz['fields'][6]['if'][2]['list_prefix'] = '<del>';
$zz['fields'][6]['if'][2]['list_suffix'] = '</del><p>';
$zz['fields'][6]['size'] = 48;
$zz['fields'][6]['class'] = 'block480a';
$zz['fields'][6]['list_append_next'] = true;
$zz['fields'][6]['link'] = [
	'area' => 'events_event',
	'fields' => 'identifier'
];
$zz['fields'][6]['typo_cleanup'] = true;
$zz['fields'][6]['typo_remove_double_spaces'] = true;

$zz['fields'][8]['field_name'] = 'abstract';
$zz['fields'][8]['type'] = 'memo';
$zz['fields'][8]['hide_in_list'] = true;
$zz['fields'][8]['rows'] = 3;
$zz['fields'][8]['explanation'] = 'Short description of the event (is displayed in the calendar under the date)';
$zz['fields'][8]['format'] = 'markdown';
$zz['fields'][8]['typo_cleanup'] = true;

$zz['fields'][24] = zzform_include('events-categories');
$zz['fields'][24]['title'] = 'Category';
$zz['fields'][24]['type'] = 'subtable';
$zz['fields'][24]['min_records'] = 1;
$zz['fields'][24]['max_records'] = 4;
$zz['fields'][24]['form_display'] = 'lines';
$zz['fields'][24]['fields'][2]['type'] = 'foreign_key';
$zz['fields'][24]['fields'][3]['show_title'] = false;
$zz['fields'][24]['fields'][4]['type'] = 'sequence';
$zz['fields'][24]['class'] = 'hidden480';
$zz['fields'][24]['list_append_next'] = true;
$zz['fields'][24]['subselect']['prefix'] = '<em>';
$zz['fields'][24]['subselect']['suffix'] = '</em>';
$zz['fields'][24]['sql'] .= ' ORDER BY events_categories.sequence';

// @deprecated
$zz['fields'][7] = zzform_include('events-contacts');
$zz['fields'][7]['title'] = 'Place';
$zz['fields'][7]['table_name'] = 'places';
$zz['fields'][7]['type'] = 'subtable';
$zz['fields'][7]['min_records'] = 1;
$zz['fields'][7]['max_records'] = 20;
$zz['fields'][7]['sql'] .= sprintf(' WHERE role_category_id = %d
	ORDER BY sequence, contact', wrap_category_id('roles/location'));
$zz['fields'][7]['form_display'] = 'lines';
$zz['fields'][7]['fields'][2]['type'] = 'foreign_key';
$zz['fields'][7]['fields'][3]['show_title'] = false;
$zz['fields'][7]['fields'][3]['sql'] = 'SELECT contact_id, contact
	FROM contacts
	LEFT JOIN categories
		ON contacts.contact_category_id = categories.category_id
	WHERE categories.parameters LIKE "%&events_location=1%"
	ORDER BY contact';
$zz['fields'][7]['fields'][3]['add_details'] = ['area' => 'contacts_places'];
$zz['fields'][7]['fields'][4]['type'] = 'hidden';
$zz['fields'][7]['fields'][4]['value'] = wrap_category_id('roles/location');
$zz['fields'][7]['fields'][4]['hide_in_form'] = true;
$zz['fields'][7]['fields'][5]['type'] = 'sequence';
$zz['fields'][7]['class'] = 'hidden480';
$zz['fields'][7]['subselect']['prefix'] = ' <em>'.wrap_text('in').' ';
$zz['fields'][7]['subselect']['suffix'] = '</em>';
$zz['fields'][7]['list_append_next'] = true;
$zz['fields'][7]['subselect']['sql'] .= sprintf(' WHERE role_category_id = %d', wrap_category_id('roles/location'));

// events-contacts
$zz['fields'][60] = [];
$zz['fields'][61] = [];
$zz['fields'][62] = [];
$zz['fields'][63] = [];
$zz['fields'][64] = [];
$zz['fields'][65] = [];
$zz['fields'][66] = [];
$zz['fields'][67] = [];
$zz['fields'][68] = [];
$zz['fields'][69] = [];

// @deprecated
$zz['fields'][61] = zzform_include('events-contacts');
$zz['fields'][61]['title'] = 'Organiser';
$zz['fields'][61]['table_name'] = 'organisers';
$zz['fields'][61]['type'] = 'subtable';
$zz['fields'][61]['min_records'] = 1;
$zz['fields'][61]['max_records'] = 10;
$zz['fields'][61]['sql'] .= sprintf(' WHERE role_category_id = %d
	ORDER BY sequence, contact', wrap_category_id('roles/organiser'));
$zz['fields'][61]['form_display'] = 'lines';
$zz['fields'][61]['fields'][2]['type'] = 'foreign_key';
$zz['fields'][61]['fields'][3]['show_title'] = false;
$zz['fields'][61]['fields'][3]['sql'] = 'SELECT contact_id, contact
	FROM contacts
	LEFT JOIN categories
		ON contacts.contact_category_id = categories.category_id
	WHERE categories.parameters LIKE "%&events_organiser=1%"
	ORDER BY contact';
$zz['fields'][61]['fields'][3]['add_details'] = 'organisers';
$zz['fields'][61]['fields'][4]['type'] = 'hidden';
$zz['fields'][61]['fields'][4]['value'] = wrap_category_id('roles/organiser');
$zz['fields'][61]['fields'][4]['hide_in_form'] = true;
$zz['fields'][61]['fields'][5]['type'] = 'sequence';
$zz['fields'][61]['class'] = 'hidden480';
$zz['fields'][61]['subselect']['sql'] .= sprintf(' WHERE role_category_id = %d', wrap_category_id('roles/organiser'));
$zz['fields'][61]['subselect']['prefix'] = '<p>'.wrap_text('Organiser').': ';
$zz['fields'][61]['subselect']['suffix'] = '</p>';

$zz['fields'][26]['title'] = 'Category';
$zz['fields'][26]['field_name'] = 'event_category_id';
$zz['fields'][26]['type'] = 'select';
$zz['fields'][26]['sql'] = 'SELECT category_id, category, description, main_category_id
	FROM /*_PREFIX_*/categories
	ORDER BY sequence, category';
$zz['fields'][26]['display_field'] = 'category';
$zz['fields'][26]['character_set'] = 'utf8';
$zz['fields'][26]['key_field_name'] = 'category_id';
$zz['fields'][26]['show_hierarchy'] = 'main_category_id';
$zz['fields'][26]['show_hierarchy_subtree'] = wrap_category_id('event');
$zz['fields'][26]['hide_in_list'] = true;
$zz['fields'][26]['hide_in_form'] = true;
$zz['fields'][26]['add_details'] = sprintf('categories?filter[maincategory]=%d', wrap_category_id('event'));
// activate only for timetable
// @todo move to events-categories
$zz['fields'][26]['type'] = 'hidden';
$zz['fields'][26]['type_detail'] = 'select';
$zz['fields'][26]['value'] = wrap_category_id('event/event');

// group
$zz['fields'][59] = [];

// author
$zz['fields'][11] = [];

$zz['fields'][20]['title'] = 'Takes place?';
$zz['fields'][20]['field_name'] = 'takes_place';
$zz['fields'][20]['type'] = 'select';
$zz['fields'][20]['enum'] = ['yes', 'no'];
$zz['fields'][20]['default'] = 'yes';
$zz['fields'][20]['hide_in_list'] = true;
$zz['fields'][20]['explanation'] = 'Better not delete cancelled appointments, but mark them so that it is clearly visible that they’re cancelled.';

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

$zz['fields'][27]['title'] = 'Following?';
$zz['fields'][27]['field_name'] = 'following';
$zz['fields'][27]['type'] = 'select';
$zz['fields'][27]['enum'] = ['yes', 'no'];
$zz['fields'][27]['default'] = 'no';
$zz['fields'][27]['hide_in_list'] = true;
$zz['fields'][27]['hide_in_form'] = true;

$zz['fields'][29]['title'] = 'Optional?';
$zz['fields'][29]['field_name'] = 'optional';
$zz['fields'][29]['type'] = 'select';
$zz['fields'][29]['enum'] = ['yes', 'no'];
$zz['fields'][29]['default'] = 'no';
$zz['fields'][29]['hide_in_list'] = true;
$zz['fields'][29]['hide_in_form'] = true;
$zz['fields'][29]['explanation'] = 'If it is possible to register for the event, mark the program item as an option.';

$zz['fields'][25]['title_tab'] = 'Seq.';
$zz['fields'][25]['field_name'] = 'sequence';
$zz['fields'][25]['type'] = 'number';
$zz['fields'][25]['auto_value'] = 'increment';
$zz['fields'][25]['hide_in_list'] = true;
$zz['fields'][25]['hide_in_list_if_empty'] = true;
$zz['fields'][25]['hide_in_form'] = true;
$zz['fields'][25]['dont_copy'] = true;
$zz['fields'][25]['explanation'] = 'Sorting if no time was specified.';

$zz['fields'][80] = zzform_include('events-media');
$zz['fields'][80]['title'] = 'Media';
$zz['fields'][80]['type'] = 'subtable';
$zz['fields'][80]['min_records'] = 1;
$zz['fields'][80]['max_records'] = wrap_setting('events_media_per_event');
$zz['fields'][80]['form_display'] = 'horizontal';
$zz['fields'][80]['sql'] .= ' ORDER BY sequence';
$zz['fields'][80]['fields'][2]['type'] = 'foreign_key';
$zz['fields'][80]['fields'][4]['type'] = 'sequence';
$zz['fields'][80]['class'] = 'hidden480';
$zz['fields'][80]['separator_before'] = true;

$zz['fields'][16] = zzform_include('eventdetails');
$zz['fields'][16]['title'] = 'Links';
$zz['fields'][16]['type'] = 'subtable';
$zz['fields'][16]['hide_in_list'] = true;
$zz['fields'][16]['min_records'] = 1;
$zz['fields'][16]['form_display'] = 'vertical';
$zz['fields'][16]['fields'][2]['type'] = 'foreign_key';
$zz['fields'][16]['fields'][6]['hide_in_form'] = true;
$zz['fields'][16]['sql'] = wrap_edit_sql($zz['fields'][16]['sql'], 'WHERE', 'active = "yes"');

$zz['fields'][18]['field_name'] = 'hashtag';
$zz['fields'][18]['prefix'] = '#';
$zz['fields'][18]['hide_in_list'] = true;

$zz['fields'][14]['title'] = 'Description';
$zz['fields'][14]['title_desc'] = '(optional)<br>';
$zz['fields'][14]['field_name'] = 'description';
$zz['fields'][14]['type'] = 'memo';
$zz['fields'][14]['hide_in_list'] = true;
$zz['fields'][14]['rows'] = 20;
$zz['fields'][14]['format'] = 'markdown';
$zz['fields'][14]['typo_cleanup'] = true;

$zz['fields'][17]['title'] = 'Registration';
$zz['fields'][17]['title_desc'] = '(optional)<br>';
$zz['fields'][17]['field_name'] = 'registration';
$zz['fields'][17]['explanation'] = 'Registration information; will be hidden after the end of the event.';
$zz['fields'][17]['type'] = 'memo';
$zz['fields'][17]['hide_in_list'] = true;
$zz['fields'][17]['rows'] = 5;
$zz['fields'][17]['format'] = 'markdown';
$zz['fields'][17]['typo_cleanup'] = true;

$zz['fields'][57] = [];

$zz['fields'][58] = [];

$zz['fields'][14]['separator'] = true;

$zz['fields'][9]['title'] = 'Main Event';
$zz['fields'][9]['field_name'] = 'main_event_id';
$zz['fields'][9]['type'] = 'select';
$zz['fields'][9]['sql'] = 'SELECT event_id, event
		, CONCAT(IFNULL(events.date_begin, ""), IFNULL(CONCAT("/", events.date_end), "")) AS duration
		, identifier
	FROM /*_PREFIX_*/events
	WHERE ISNULL(main_event_id)
	ORDER BY identifier DESC';
$zz['fields'][9]['sql_format'][2] = 'wrap_date';
$zz['fields'][9]['key_field_name'] = 'event_id'; // for subtitle!
$zz['fields'][9]['hide_in_list'] = true;

if (wrap_access('events_parameters')) {
	$zz['fields'][28]['title'] = 'Parameters';
	$zz['fields'][28]['field_name'] = 'parameters';
	$zz['fields'][28]['search'] = 'events.parameters';
	$zz['fields'][28]['type'] = 'parameter';
	$zz['fields'][28]['hide_in_list'] = true;
}

$zz['fields'][2]['field_name'] = 'identifier';
$zz['fields'][2]['type'] = 'identifier';
$zz['fields'][2]['fields'] = [
	'event_year', 'date_begin{0,4}', 'date_end{0,4}', 'event', 'identifier'
];
$zz['fields'][2]['identifier']['ignore_this_if']['date_begin{0,4}'] = 'event_year';
$zz['fields'][2]['identifier']['ignore_this_if']['date_end{0,4}'] = 'date_begin{0,4}';
$zz['fields'][2]['identifier']['concat'] = ['/'];
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
	, DATE_FORMAT(/*_PREFIX_*/events.time_begin, "%H:%i") AS time_begin
	, DATE_FORMAT(/*_PREFIX_*/events.time_end, "%H:%i") AS time_end
	, /*_PREFIX_*/categories.category
	FROM /*_PREFIX_*/events
	LEFT JOIN /*_PREFIX_*/events_categories USING (event_id)
	LEFT JOIN /*_PREFIX_*/events_contacts USING (event_id)
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/events.event_category_id = /*_PREFIX_*/categories.category_id
';

$zz['sqlorder'] = ' ORDER BY /*_PREFIX_*/events.date_begin DESC, IFNULL(/*_PREFIX_*/events.time_begin, /*_PREFIX_*/events.time_end) DESC, /*_PREFIX_*/events.sequence DESC, /*_PREFIX_*/events.identifier DESC';

$zz['conditions'][1]['scope'] = 'record';
$zz['conditions'][1]['where'] = '/*_PREFIX_*/events.takes_place = "no"';

$zz['conditions'][2]['scope'] = 'record';
$zz['conditions'][2]['where'] = '/*_PREFIX_*/events.published = "no"';

$zz['hooks']['before_insert'][] = 'mf_events_date_check';
$zz['hooks']['before_update'][] = 'mf_events_date_check';

$zz['filter'][1]['sql'] = 'SELECT DISTINCT YEAR(date_begin) AS year_idf
		, YEAR(date_begin) AS year
	FROM events
	ORDER BY YEAR(date_begin) DESC';
$zz['filter'][1]['title'] = wrap_text('Year');
$zz['filter'][1]['identifier'] = 'year';
$zz['filter'][1]['type'] = 'list';
$zz['filter'][1]['where'] = 'YEAR(/*_PREFIX_*/events.date_begin)';

$zz['filter'][2]['sql'] = 'SELECT DISTINCT category_id
		, category
	FROM events_categories
	LEFT JOIN categories USING (category_id)
	ORDER BY category';
$zz['filter'][2]['title'] = wrap_text('Category');
$zz['filter'][2]['identifier'] = 'category';
$zz['filter'][2]['type'] = 'list';
$zz['filter'][2]['where'] = '/*_PREFIX_*/events_categories.category_id';

$zz['filter'][3]['sql'] = sprintf('SELECT DISTINCT contact_id
		, IFNULL(contact_short, contact) AS contact
	FROM events_contacts
	LEFT JOIN contacts USING (contact_id)
	WHERE events_contacts.role_category_id = %d
	ORDER BY contact', wrap_category_id('roles/organiser'));
$zz['filter'][3]['title'] = wrap_text('Organiser');
$zz['filter'][3]['identifier'] = 'organiser';
$zz['filter'][3]['type'] = 'list';
$zz['filter'][3]['where'] = '/*_PREFIX_*/events_contacts.contact_id';

$zz['filter'][4]['title'] = wrap_text('Published');
$zz['filter'][4]['identifier'] = 'published';
$zz['filter'][4]['type'] = 'list';
$zz['filter'][4]['where'] = '/*_PREFIX_*/events.published';
$zz['filter'][4]['selection']['yes'] = wrap_text('yes');
$zz['filter'][4]['selection']['no'] = wrap_text('no');

$zz['record']['copy'] = true;

$zz['subtitle']['main_event_id']['sql'] = $zz['fields'][9]['sql'];
$zz['subtitle']['main_event_id']['var'] = ['event', 'duration'];
$zz['subtitle']['main_event_id']['format'][1] = 'wrap_date';
$zz['subtitle']['main_event_id']['link'] = '../';
$zz['subtitle']['main_event_id']['link_no_append'] = true;

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
			, DATE_FORMAT(/*_PREFIX_*/events.time_begin, "%H:%i") AS time_begin
			, DATE_FORMAT(/*_PREFIX_*/events.time_end, "%H:%i") AS time_end
			, /*_PREFIX_*/categories.category
			, /*_PREFIX_*/websites.domain
		FROM /*_PREFIX_*/events
		LEFT JOIN /*_PREFIX_*/events_categories USING (event_id)
		LEFT JOIN /*_PREFIX_*/events_contacts USING (event_id)
		LEFT JOIN /*_PREFIX_*/categories
			ON /*_PREFIX_*/events.event_category_id = /*_PREFIX_*/categories.category_id
		LEFT JOIN /*_PREFIX_*/websites USING (website_id) 
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
