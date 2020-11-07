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
 * Report preferences.
 *
 * @package    local_time_tracking
 * @copyright  2020 NYIAJ LLC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_time_tracking\form;

use coding_exception;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * Report preferences.
 *
 * @package local_time_tracking
 */
class report_preferences_form extends \moodleform {

    /**
     * Define form fields.
     *
     * @throws coding_exception
     */
    public function definition() {
        $mform =& $this->_form;

        $mform->addElement('header', 'preferencesuser', get_string('preferencesuser', 'scorm'));

        $mform->addElement('select', 'pagesize', get_string('pagesize', 'scorm'), [
            -1 => get_string('all'),
            10 => '10',
            25 => '25',
            50 => '50',
            100 => '100'
        ]);
        $mform->setType('pagesize', PARAM_INT);
        $mform->setDefault('pagesize', 25);

        $mform->addElement('submit', 'submitbutton', get_string('savepreferences'));
    }
}
