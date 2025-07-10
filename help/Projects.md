<!--
# events module
# projects
#
# Part of »Zugzwang Project«
# https://www.zugzwang.org/modules/events
#
# @author Gustaf Mossakowski <gustaf@koenige.org>
# @copyright Copyright © 2025 Gustaf Mossakowski
# @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
#
-->

# Projects

Projects are saved in the events module as events, but with different
links and in different categories.

## Categories

Projects have their own tree in the `categories` table. The main
category, `projects`, needs to be created manually. If you name it
differently, it has to have the parameter `alias: projects`. It is
possible to either just use one field with a list of categories per
project or to create several separate list that are shown as separate
fields and can be handled differently.

### Using one field

…

### Using several fields

Here, the `projects` category gets the parameter `use_subtree: 1`.

At first, you’ll need to add a list of fields. The `sequence` field can
be used to set the order of the fields in the form. The field categories
can have several parameters:

* `max_records: 1` – Only one category can be chosen per field, set to a
higher value means that n categories can be selected.

* `min_records_required: 1` - It is required to chose at least one
category in this field.

* `own_type_category: 1` – Write this field category to the
`type_category_id` field in the database.

* `sequence: 0` – Do not show a `sequence` field.

* `property: 1` – Show a field for a property value next to the
category. Setting a property means you can add further information for
each child category, e. g. `type: url` if a property is meant to be a
URL.

* `hide_in_list` – Do not show this field in the list view.
  
Per default, all categories are shown in the list view in one column,
prefixed by the field category.
