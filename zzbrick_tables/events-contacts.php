<?php 

/**
 * events module
 * Table definition for 'events/media'
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2012, 2014, 2016, 2018-2022 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Events/Contacts';
$zz['table'] = '/*_PREFIX_*/events_contacts';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'event_contact_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'event_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = sprintf('SELECT event_id
		, CONCAT(event, " (", DATE_FORMAT(date_begin, "%s"), ")") AS event
		, identifier
	FROM /*_PREFIX_*/events
	WHERE ISNULL(main_event_id)
	ORDER BY date_begin DESC', wrap_placeholder('mysql_date_format'));
$zz['fields'][2]['sql_character_set'][1] = 'utf8';
$zz['fields'][2]['display_field'] = 'event';
$zz['fields'][2]['search'] = sprintf('CONCAT(/*_PREFIX_*/events.event, " (", 
	DATE_FORMAT(/*_PREFIX_*/events.date_begin, "%s"), ")")', wrap_placeholder('mysql_date_format'));

$zz['fields'][5]['title'] = 'No.';
$zz['fields'][5]['field_name'] = 'sequence';
$zz['fields'][5]['type'] = 'number';
$zz['fields'][5]['auto_value'] = 'increment';
$zz['fields'][5]['def_val_ignore'] = true;

$zz['fields'][3]['title'] = 'Contact';
$zz['fields'][3]['field_name'] = 'contact_id';
$zz['fields'][3]['type'] = 'select';
$zz['fields'][3]['sql'] = 'SELECT contact_id, contact
	FROM /*_PREFIX_*/contacts
	ORDER BY contact';
$zz['fields'][3]['search'] = '/*_PREFIX_*/contacts.contact';
$zz['fields'][3]['display_field'] = 'contact';

$zz['fields'][4]['title'] = 'Role';
$zz['fields'][4]['field_name'] = 'role_category_id';
$zz['fields'][4]['key_field_name'] = 'category_id';
$zz['fields'][4]['type'] = 'select';
$zz['fields'][4]['sql'] = 'SELECT category_id, category, main_category_id
	FROM /*_PREFIX_*/categories
	ORDER BY sequence, category';
$zz['fields'][4]['display_field'] = 'category';
$zz['fields'][4]['show_hierarchy'] = 'main_category_id';
$zz['fields'][4]['exclude_from_search'] = true;
$zz['fields'][4]['def_val_ignore'] = true;
$zz['fields'][4]['show_hierarchy_subtree'] = wrap_category_id('roles');

$zz['fields'][99]['field_name'] = 'last_update';
$zz['fields'][99]['type'] = 'timestamp';
$zz['fields'][99]['hide_in_list'] = true;

$zz['subselect']['sql'] = 'SELECT event_id, IFNULL(contact_short, contact) AS contact
	FROM /*_PREFIX_*/events_contacts
	LEFT JOIN /*_PREFIX_*/contacts USING (contact_id)
';
$zz['subselect']['concat_rows'] = ', ';

$zz['sql'] = sprintf('SELECT /*_PREFIX_*/events_contacts.*
		, CONCAT(/*_PREFIX_*/events.event, " (", IFNULL(DATE_FORMAT(events.date_begin, "%s"), ""), IFNULL(CONCAT("–", DATE_FORMAT(events.date_end, "%s")), ""), ")") AS event
		, contact
		, category
	FROM /*_PREFIX_*/events_contacts
	LEFT JOIN /*_PREFIX_*/contacts USING (contact_id)
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/categories.category_id = /*_PREFIX_*/events_contacts.role_category_id
	LEFT JOIN /*_PREFIX_*/events
		ON /*_PREFIX_*/events_contacts.event_id = /*_PREFIX_*/events.event_id
'
	, wrap_placeholder('mysql_date_format')
	, wrap_placeholder('mysql_date_format')
);
$zz['sqlorder'] = ' ORDER BY IFNULL(date_begin, date_end), time_begin DESC, events.identifier, contact';
