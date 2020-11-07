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
 * Configure settings.
 *
 * @package    local_time_tracking
 * @copyright  2020 NYIAJ LLC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_time_tracking\local\report\course_report;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$courseid = required_param('courseid', PARAM_INT);
$download = optional_param('download', '', PARAM_ALPHA);

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($courseid);
$title = get_string('coursereport', 'local_time_tracking', $course);

$PAGE->set_url(new moodle_url('/local/time_tracking/coursereport.php', ['courseid' => $courseid]));
$PAGE->set_context($context);
$PAGE->set_heading($title);
$PAGE->set_title($title);

require_login($courseid);
require_capability('local/time_tracking:viewreports', $context);

$report = new course_report($courseid, $download);
$report->define_baseurl($PAGE->url);
$report->is_downloadable(true);
$report->show_download_buttons_at([TABLE_P_BOTTOM]);

$form = new \local_time_tracking\form\report_preferences_form($PAGE->url);

$pagesize = get_user_preferences('local_time_tracking_pro_pagesize', 25);

if ($data = $form->get_data()) {
    $pagesize = $data->pagesize;
    set_user_preference('local_time_tracking_pro_pagesize', $data->pagesize);
} else {
    $form->set_data(['pagesize' => $pagesize]);
}

// If downloading get all records.
if ($report->is_downloading()) {
    $pagesize = -1;
}

// Output report content before header to allow download.
ob_start();
$report->out($pagesize, true);
$tablehtml = ob_get_contents();
ob_end_clean();

echo $OUTPUT->header();
echo $tablehtml;
$form->display();
echo $OUTPUT->footer();