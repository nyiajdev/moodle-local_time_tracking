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
 * Strings for component 'local_time_tracking', language 'en'
 *
 * @package   local_time_tracking
 * @copyright 2020 NYIAJ LLC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['activitymodulecount'] = '{$a->count} activity module(s)';
$string['activitymodules'] = 'Activity modules';
$string['generalsettings'] = 'General settings';
$string['globalsettings'] = 'Site settings';
$string['idlethreshold'] = 'User is considered inactive after';
$string['idlethreshold_help'] = 'User is considered inactive after this many seconds without any action (mouse movement, mouse click, keyboard press, etc). Set to 0 to disable inactivity.';
$string['interval'] = 'Record user activity every';
$string['interval_help'] = 'Record user activity every X seconds by making an AJAX request. A lower value will track time more accurately, but will cause more load on the server. Leave this at the 10 second default unless you have a specific reason (such as having short pages, too much server load, etc).';
$string['notimetrackedyet'] = 'No time tracked yet';
$string['notmysession'] =' Time tracking session is not yours.';
$string['pluginname'] = 'Time Tracking';
$string['reportdateformat'] = 'Report date format';
$string['reportdateformat_help'] = 'Choose how dates will be displayed in Time Tracking reports.';
$string['seconds'] = 'seconds';
$string['secondsafternoactivity'] = 'seconds of no activity';
$string['secondsbeforelogout'] = 'seconds before logout due to inactivity';
$string['sessionnotfound'] = 'Time tracking session not found.';
$string['sessiontimeout'] = 'Logout the user after';
$string['sessiontimeout_help'] = 'The user may become idle and after this many seconds, automatically logs out user and redirects to login page.';
$string['sessiontimeoutwarnthreshold'] = 'Warn users';
$string['sessiontimeoutwarnthreshold_help'] = 'Enter seconds before session timeout to warn user with a popup. Example, 60 will warn user a minute before they logout to become active again. Set to 0 to not display popup.';
$string['settingshelp'] = '<p class="alert alert-info"></p>';
$string['settingsprovider'] = 'Settings provider';
$string['settingsprovider_help'] = 'Choose where you will configure the Time Tracking settings.';
$string['settingsproviderlabel'] = 'Settings provider';
$string['totaltime'] = 'Total time';
$string['totaltimeincourse'] = 'Total time spent in course';
$string['trackerenabled'] = 'Enable time tracking';
$string['trackerenabled_help'] = 'Globally enable or disable time tracking for all users. If disabled, user activity will no longer be recorded, but reports will still be available for prior recording. This also acts as a kill switch for the Time Tracking plugin.';
$string['usstandarddate'] = '%m-%d-%Y %I:%M %p';
$string['time_tracking:viewreports'] = 'View Time Tracking reports';
$string['coursereport'] = '{$a->shortname} Time Tracking report';