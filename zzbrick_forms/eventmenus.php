<?php 

/**
 * events module
 * Form for 'eventmenus'
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2022-2023, 2025 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


if (empty($brick['data']['event_id'])) wrap_quit(404);

$zz = zzform_include('eventmenus');

$zz['where']['event_id'] = $brick['data']['event_id'];

$zz['fields'][5]['type'] = 'sequence';
$zz['fields'][5]['auto_value'] = 'increment';

$zz['subtitle']['event_id']['sql'] = $zz['fields'][2]['sql'];
$zz['subtitle']['event_id']['var'] = ['event', 'duration'];
$zz['subtitle']['event_id']['format'][1] = 'wrap_date';
$zz['subtitle']['event_id']['link'] = '../';
$zz['subtitle']['event_id']['link_no_append'] = true;
