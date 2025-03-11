<?php 

/**
 * events module
 * Form for 'events'
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


if (empty($brick['data']['event_id'])) wrap_quit(404);

$zz = zzform_include('events', [], 'forms');
$zz['where']['event_id'] = $brick['data']['event_id'];

$zz['access'] = 'edit_only';
$zz['page']['referer'] = '../';
