<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Time tracking plugin upgrade code
 *
 * @package    local_time_tracking
 * @copyright  2020 NYIAJ LLC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Function to upgrade auth_db.
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_local_time_tracking_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2020082001) {

        // Define table time_tracking_session to be created.
        $table = new xmldb_table('time_tracking_session');

        // Adding fields to table time_tracking_session.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('contextid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('elapsedtime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table time_tracking_session.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('usermodified', XMLDB_KEY_FOREIGN, ['usermodified'], 'user', ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_key('contextid', XMLDB_KEY_FOREIGN, ['contextid'], 'context', ['id']);

        // Conditionally launch create table for time_tracking_session.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Time_tracking savepoint reached.
        upgrade_plugin_savepoint(true, 2020082001, 'local', 'time_tracking');
    }

    if ($oldversion < 2020082200) {

        // Define field relatedcourseid to be added to time_tracking_session.
        $table = new xmldb_table('time_tracking_session');
        $field = new xmldb_field('relatedcourseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '1', 'usermodified');

        // Conditionally launch add field relatedcourseid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field ipaddress to be added to time_tracking_session.
        $table = new xmldb_table('time_tracking_session');
        $field = new xmldb_field('ipaddress', XMLDB_TYPE_CHAR, '45', null, XMLDB_NOTNULL, null, null, 'relatedcourseid');

        // Conditionally launch add field ipaddress.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Time_tracking savepoint reached.
        upgrade_plugin_savepoint(true, 2020082200, 'local', 'time_tracking');
    }

    if ($oldversion < 2020090301) {
        $table = new xmldb_table('time_tracking_session');
        $dbman->rename_table($table, 'local_time_tracking_session');

        upgrade_plugin_savepoint(true, 2020090301, 'local', 'time_tracking');
    }

    return true;
}
