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

use local_time_tracking\form\configure_form;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('localtimetrackingconfigure');

$PAGE->set_title(get_string('pluginname', 'local_time_tracking'));
$PAGE->set_heading(get_string('pluginname', 'local_time_tracking'));

$form = new configure_form();

if ($data = $form->get_data()) {
    foreach ($data as $name => $value) {
        if ($name == 'submitbutton') {
            continue;
        }
        set_config($name, $value, 'local_time_tracking');
    }

    \core\notification::success(get_string('changessaved'));
} else {
    $form->set_data(get_config('local_time_tracking'));
}

echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();

