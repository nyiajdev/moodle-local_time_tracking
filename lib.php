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
 * Common functions and callbacks.
 *
 * @package    local_time_tracking
 * @copyright  2020 NYIAJ LLC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Use navigation callback to initiate tracking JS.
 *
 * @param global_navigation $nav
 */
function local_time_tracking_extend_navigation(global_navigation $nav) {
    global $PAGE, $USER;

    if (is_siteadmin() || AJAX_SCRIPT || CLI_SCRIPT || WS_SERVER) {
        return;
    }

    try {
        if (get_config('local_time_tracking', 'trackerenabled') && $PAGE->context) {
            if (has_capability('local/time_tracking:trackactivity', $PAGE->context)) {
                $tracker = new \local_time_tracking\local\tracker($PAGE->context, $USER->id);
                $tracker->start_session();
            }
        }
    } catch (moodle_exception $e) {
        // Catch any exceptions to prevent page from breaking for users.
        debugging($e->getMessage());
    }
}

/**
 * This function extends the course navigation.
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 * @throws moodle_exception
 */
function local_time_tracking_extend_navigation_course($navigation, $course, $context) {
    if ($node = $navigation->get('coursereports')) {
        $url = new moodle_url('/local/time_tracking/coursereport.php', ['courseid' => $course->id]);
        $node->add(get_string('pluginname', 'local_time_tracking'), $url, navigation_node::TYPE_SETTING, null, null,
            new pix_icon('i/report', ''));
    }
}

/**
 * Format date based on format defined in settings.
 *
 * @param int $timestamp
 * @return string
 * @throws coding_exception
 * @throws dml_exception
 */
function local_time_tracking_format_date(int $timestamp) {
    if ($format = get_config('local_time_tracking', 'reportdateformat')) {
        $component = strpos($format, 'strf') === 0 ? '' : 'local_time_tracking';
    } else {
        $format = 'usstandarddate';
        $component = 'local_time_tracking';
    }

    return userdate($timestamp, get_string($format, $component));
}

/**
 * Format seconds as time.
 *
 * @param int $seconds
 * @return string
 */
function local_time_tracking_format_elapsed_time(int $seconds): string {
    return sprintf('%02d:%02d:%02d', ($seconds / 3600), ($seconds / 60 % 60), $seconds % 60);
}