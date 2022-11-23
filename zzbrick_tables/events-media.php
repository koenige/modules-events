<?php 

/**
 * events module
 * Table definition for 'events/media'
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2010, 2013-2015, 2017-2022 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Media of an Event';
$zz['table'] = '/*_PREFIX_*/events_media';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'event_medium_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'event_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = sprintf('SELECT event_id
		, event
		, CONCAT(IFNULL(events.date_begin, ""), IFNULL(CONCAT("/", events.date_end), "")) AS duration
		, identifier
	FROM /*_PREFIX_*/events
	WHERE ISNULL(main_event_id)
	ORDER BY identifier DESC', wrap_placeholder('mysql_date_format'));
$zz['fields'][2]['sql_format'][2] = 'wrap_date';
$zz['fields'][2]['display_field'] = 'event';
$zz['fields'][2]['search'] = sprintf('CONCAT(/*_PREFIX_*/events.event, " (", 
	DATE_FORMAT(IFNULL(/*_PREFIX_*/events.date_begin, /*_PREFIX_*/events.date_end), "%s"), ")")', wrap_placeholder('mysql_date_format'));

$zz['fields'][4]['title'] = 'No.';
$zz['fields'][4]['field_name'] = 'sequence';
$zz['fields'][4]['type'] = 'number';
$zz['fields'][4]['auto_value'] = 'increment';
$zz['fields'][4]['def_val_ignore'] = true;

$zz['fields'][5]['title'] = 'Preview';
$zz['fields'][5]['field_name'] = 'image';
$zz['fields'][5]['type'] = 'image';
$zz['fields'][5]['class'] = 'preview';
$zz['fields'][5]['path'] = [
	'root' => $zz_setting['media_folder'], 
	'webroot' => $zz_setting['files_path'],
	'string1' => '/',
	'field1' => 'filename',
	'string2' => '.',
	'string3' => wrap_get_setting('media_preview_size'),
	'string4' => '.',
	'extension' => 'thumb_extension',
	'webstring1' => '?v=',
	'webfield1' => 'version'
];

$zz['fields'][3]['title'] = 'Medium';
$zz['fields'][3]['field_name'] = 'medium_id';
$zz['fields'][3]['type'] = 'select';
$zz['fields'][3]['sql'] = sprintf('SELECT /*_PREFIX_*/media.medium_id, folders.title AS folder
		, CONCAT("[", /*_PREFIX_*/media.medium_id, "] ", /*_PREFIX_*/media.title) AS image
	FROM /*_PREFIX_*/media 
	LEFT JOIN /*_PREFIX_*/media folders
		ON /*_PREFIX_*/media.main_medium_id = folders.medium_id
	WHERE /*_PREFIX_*/media.filetype_id != %d
	ORDER BY folders.title, /*_PREFIX_*/media.filename', wrap_filetype_id('folder'));
$zz['fields'][3]['sql_character_set'][1] = 'utf8';
$zz['fields'][3]['sql_character_set'][2] = 'utf8';
$zz['fields'][3]['id_field_name'] = '/*_PREFIX_*/media.medium_id';
$zz['fields'][3]['display_field'] = 'image';
$zz['fields'][3]['group'] = 'folder';
$zz['fields'][3]['exclude_from_search'] = true;

$zz['fields'][99]['field_name'] = 'last_update';
$zz['fields'][99]['type'] = 'timestamp';
$zz['fields'][99]['hide_in_list'] = true;

$zz['subselect']['sql'] = 'SELECT event_id, filename, t_mime.extension, version
	FROM /*_PREFIX_*/events_media
	LEFT JOIN /*_PREFIX_*/media USING (medium_id)
	LEFT JOIN /*_PREFIX_*/filetypes AS o_mime USING (filetype_id)
	LEFT JOIN /*_PREFIX_*/filetypes AS t_mime 
		ON /*_PREFIX_*/media.thumb_filetype_id = t_mime.filetype_id
	WHERE t_mime.mime_content_type = "image"
	AND /*_PREFIX_*/events_media.sequence = 1
';
$zz['subselect']['concat_fields'] = '';
$zz['subselect']['field_suffix'][0] = '.'.wrap_get_setting('media_preview_size').'.';
$zz['subselect']['field_suffix'][1] = '?v=';
$zz['subselect']['prefix'] = '<img src="'.$zz_setting['files_path'].'/';
$zz['subselect']['suffix'] = '">';
$zz['subselect']['dont_mark_search_string'] = true;

$zz['sql'] = sprintf('SELECT /*_PREFIX_*/events_media.*
		, CONCAT(event, " (", IFNULL(DATE_FORMAT(date_begin, "%s"), ""), IFNULL(CONCAT("–", DATE_FORMAT(date_end, "%s")), ""), ")") AS event
		, CONCAT("[", /*_PREFIX_*/media.medium_id, "] ", /*_PREFIX_*/media.title) AS image
		, /*_PREFIX_*/media.filename, version
		, t_mime.extension AS thumb_extension
	FROM /*_PREFIX_*/events_media
	LEFT JOIN /*_PREFIX_*/media USING (medium_id)
	LEFT JOIN /*_PREFIX_*/filetypes AS t_mime 
		ON /*_PREFIX_*/media.thumb_filetype_id = t_mime.filetype_id
	LEFT JOIN /*_PREFIX_*/events USING (event_id)
'
	, wrap_placeholder('mysql_date_format')
	, wrap_placeholder('mysql_date_format')
);
$zz['sqlorder'] = ' ORDER BY IFNULL(date_begin, date_end) DESC, time_begin DESC, events.identifier, sequence';
