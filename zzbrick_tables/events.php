<?php 

/**
 * events module
 * Table definition for 'events'
 *
 * Part of »Zugzwang Project«
 * http://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2005-2020 Gustaf Mossakowski
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

$zz['fields'][56]['field_name'] = 'timezone';

$zz['fields'][6]['field_name'] = 'event';
$zz['fields'][6]['type'] = 'text';
$zz['fields'][6]['list_prefix'] = '<strong>';
$zz['fields'][6]['list_suffix'] = '</strong>';
$zz['fields'][6]['if'][1]['list_prefix'] = '<del>';
$zz['fields'][6]['if'][1]['list_suffix'] = '</del> (cancelled)';
$zz['fields'][6]['size'] = 48;
$zz['fields'][6]['class'] = 'block480a';
$zz['fields'][6]['list_append_next'] = true;
$zz['fields'][6]['link'] = [
	'string1' => $zz_setting['base'].$zz_setting['events_path'].'/',
	'field1' => 'identifier',
	'string2' => '/'
];

$zz['fields'][8]['field_name'] = 'abstract';
$zz['fields'][8]['type'] = 'memo';
$zz['fields'][8]['hide_in_list'] = true;
$zz['fields'][8]['rows'] = 3;
$zz['fields'][8]['explanation'] = 'Short description of the event (is displayed in the calendar under the date)';
$zz['fields'][8]['format'] = 'markdown';

$zz['fields'][7] = zzform_include_table('events-contacts');
$zz['fields'][7]['title'] = 'Place';
$zz['fields'][7]['table_name'] = 'places';
$zz['fields'][7]['type'] = 'subtable';
$zz['fields'][7]['min_records'] = 1;
$zz['fields'][7]['max_records'] = 5;
$zz['fields'][7]['sql'] .= sprintf(' WHERE role_category_id = %d', wrap_category_id('roles/location'));
$zz['fields'][7]['form_display'] = 'lines';
$zz['fields'][7]['fields'][2]['type'] = 'foreign_key';
$zz['fields'][7]['fields'][3]['show_title'] = false;
$zz['fields'][7]['fields'][3]['sql'] = sprintf('SELECT contact_id, contact
	FROM contacts
	WHERE contact_category_id = %d
	ORDER BY contact', wrap_category_id('contact/place'));
$zz['fields'][7]['fields'][4]['type'] = 'hidden';
$zz['fields'][7]['fields'][4]['value'] = wrap_category_id('roles/location');
$zz['fields'][7]['fields'][4]['hide_in_form'] = true;
$zz['fields'][7]['class'] = 'hidden480';
$zz['fields'][7]['subselect']['prefix'] = '<p><em>'.wrap_text('in').' ';
$zz['fields'][7]['subselect']['suffix'] = '</em></p>';
$zz['fields'][7]['list_append_next'] = true;
$zz['fields'][7]['subselect']['sql'] .= sprintf(' WHERE role_category_id = %d', wrap_category_id('roles/location'));

$zz['fields'][64] = zzform_include_table('events-contacts');
$zz['fields'][64]['title'] = 'Organiser';
$zz['fields'][64]['table_name'] = 'organisers';
$zz['fields'][64]['type'] = 'subtable';
$zz['fields'][64]['min_records'] = 1;
$zz['fields'][64]['max_records'] = 4;
$zz['fields'][64]['sql'] .= sprintf(' WHERE role_category_id = %d', wrap_category_id('roles/organiser'));
$zz['fields'][64]['form_display'] = 'lines';
$zz['fields'][64]['fields'][2]['type'] = 'foreign_key';
$zz['fields'][64]['fields'][3]['show_title'] = false;
$zz['fields'][64]['fields'][4]['type'] = 'hidden';
$zz['fields'][64]['fields'][4]['value'] = wrap_category_id('roles/organiser');
$zz['fields'][64]['fields'][4]['hide_in_form'] = true;
$zz['fields'][64]['class'] = 'hidden480';
$zz['fields'][64]['subselect']['sql'] .= sprintf(' WHERE role_category_id = %d', wrap_category_id('roles/organiser'));
$zz['fields'][64]['subselect']['prefix'] = '<p>'.wrap_text('Organiser').': ';
$zz['fields'][64]['subselect']['suffix'] = '</p>';

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
$zz['fields'][26]['show_hierarchy_subtree'] = wrap_category_id('events');
$zz['fields'][26]['hide_in_list'] = true;

$zz['fields'][63] = zzform_include_table('events-categories');
$zz['fields'][63]['title'] = 'Category';
$zz['fields'][63]['type'] = 'subtable';
$zz['fields'][63]['min_records'] = 1;
$zz['fields'][63]['max_records'] = 4;
$zz['fields'][63]['form_display'] = 'lines';
$zz['fields'][63]['fields'][2]['type'] = 'foreign_key';
$zz['fields'][63]['fields'][3]['show_title'] = false;
$zz['fields'][63]['class'] = 'hidden480';
$zz['fields'][63]['hide_in_list'] = true;

// group
$zz['fields'][59] = [];

// author
$zz['fields'][11] = [];

$zz['fields'][10]['title_tab'] = 'Web';
$zz['fields'][10]['field_name'] = 'published';
$zz['fields'][10]['type'] = 'select';
$zz['fields'][10]['enum'] = ['yes', 'no'];
$zz['fields'][10]['default'] = 'yes';
$zz['fields'][10]['class'] = 'hidden480';

$zz['fields'][20]['title'] = 'Takes place?';
$zz['fields'][20]['field_name'] = 'takes_place';
$zz['fields'][20]['type'] = 'select';
$zz['fields'][20]['enum'] = ['yes', 'no'];
$zz['fields'][20]['default'] = 'yes';
$zz['fields'][20]['hide_in_list'] = true;
$zz['fields'][20]['explanation'] = 'Better not delete cancelled appointments, but mark them so that it is clearly visible that they’re cancelled.';

$zz['fields'][23]['title_tab'] = 'N?';
$zz['fields'][23]['title'] = 'News?';
$zz['fields'][23]['explanation'] = 'Should the event appear in the news?';
$zz['fields'][23]['field_name'] = 'show_in_news';
$zz['fields'][23]['type'] = 'select';
$zz['fields'][23]['enum'] = ['yes', 'no'];
$zz['fields'][23]['default'] = 'no';
$zz['fields'][23]['class'] = 'hidden480';
$zz['fields'][23]['separator'] = true;

$zz['fields'][24]['title'] = 'Following?';
$zz['fields'][24]['field_name'] = 'following';
$zz['fields'][24]['type'] = 'select';
$zz['fields'][24]['enum'] = ['yes', 'no'];
$zz['fields'][24]['default'] = 'no';
$zz['fields'][24]['hide_in_list'] = true;
$zz['fields'][24]['hide_in_form'] = true;

$zz['fields'][25]['title_tab'] = 'Seq.';
$zz['fields'][25]['field_name'] = 'sequence';
$zz['fields'][25]['type'] = 'number';
$zz['fields'][25]['auto_value'] = 'increment';
$zz['fields'][25]['hide_in_list'] = true;
$zz['fields'][25]['hide_in_list_if_empty'] = true;
$zz['fields'][25]['hide_in_form'] = true;
$zz['fields'][25]['explanation'] = 'Sorting if no time was specified.';

$zz['fields'][62] = zzform_include_table('events-media');
$zz['fields'][62]['title'] = 'Media';
$zz['fields'][62]['type'] = 'subtable';
$zz['fields'][62]['min_records'] = 1;
$zz['fields'][62]['max_records'] = 10;
$zz['fields'][62]['form_display'] = 'horizontal';
$zz['fields'][62]['sql'] .= ' ORDER BY sequence';
$zz['fields'][62]['fields'][2]['type'] = 'foreign_key';
$zz['fields'][62]['class'] = 'hidden480';

$zz['fields'][16]['title'] = 'Direct Link';
$zz['fields'][16]['field_name'] = 'direct_link';
$zz['fields'][16]['type'] = 'url';
$zz['fields'][16]['hide_in_list'] = true;
$zz['fields'][16]['explanation'] = 'Only if the full description of the event is on another website.';

$zz['fields'][14]['title'] = 'Description';
$zz['fields'][14]['title_desc'] = '(optional)<br>';
$zz['fields'][14]['field_name'] = 'description';
$zz['fields'][14]['type'] = 'memo';
$zz['fields'][14]['hide_in_list'] = true;
$zz['fields'][14]['rows'] = 20;
$zz['fields'][14]['format'] = 'markdown';

$zz['fields'][17]['title'] = 'Registration';
$zz['fields'][17]['title_desc'] = '(optional)<br>';
$zz['fields'][17]['field_name'] = 'registration';
$zz['fields'][17]['type'] = 'memo';
$zz['fields'][17]['hide_in_list'] = true;
$zz['fields'][17]['rows'] = 5;
$zz['fields'][17]['format'] = 'markdown';

$zz['fields'][57] = [];

$zz['fields'][58] = [];

$zz['fields'][14]['separator'] = true;

$zz['fields'][59]['title'] = 'Main Event';
$zz['fields'][59]['field_name'] = 'main_event_id';
$zz['fields'][59]['type'] = 'select';
$zz['fields'][59]['sql'] = 'SELECT event_id, begin, event
	FROM events
	ORDER BY begin DESC';
$zz['fields'][59]['key_field_name'] = 'event_id'; // für subtitle!
$zz['fields'][59]['sql'] = 'SELECT event_id, event, main_event_id, identifier
	FROM events
	ORDER BY identifier';
$zz['fields'][59]['hide_in_list'] = true;

$zz['fields'][2]['field_name'] = 'identifier';
$zz['fields'][2]['type'] = 'identifier';
$zz['fields'][2]['fields'] = [
	'date_begin{0,4}','event', 'identifier'
];
$zz['fields'][2]['conf_identifier']['concat'] = ['/', '-', '/'];
$zz['fields'][2]['conf_identifier']['exists'] = '-';
$zz['fields'][2]['hide_in_list'] = true;

$zz['fields'][21]['field_name'] = 'created';
$zz['fields'][21]['type'] = 'hidden';
$zz['fields'][21]['type_detail'] = 'datetime';
$zz['fields'][21]['default'] = date('Y-m-d H:i:s');
$zz['fields'][21]['hide_in_list'] = true;
$zz['fields'][21]['dont_copy'] = true;

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
';

$zz['sqlorder'] = ' ORDER BY date_begin DESC, IFNULL(time_begin, time_end) DESC, sequence DESC, identifier DESC';

$zz['conditions'][1]['scope'] = 'record';
$zz['conditions'][1]['where'] = 'takes_place = "no"';

$zz['hooks']['before_insert'][] = 'mod_events_date_check';
$zz['hooks']['before_update'][] = 'mod_events_date_check';

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
