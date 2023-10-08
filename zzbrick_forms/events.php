<?php 

/**
 * events module
 * Form for 'eventmenus'
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$values = $values ?? [];
$zz = zzform_include('events', $values);

//
// events_contacts
//

$zz['fields'][7] = []; // places
$zz['fields'][61] = []; // organisers

if (in_array('contacts', wrap_setting('modules'))) {
	$values['roles_restrict_to'] = 'events';
	mf_default_categories_restrict($values, 'roles');

	$no = 60;
	foreach ($values['roles'] as $role)
		mf_contacts_contacts_subtable($zz, 'events', $role, $no++);
}
