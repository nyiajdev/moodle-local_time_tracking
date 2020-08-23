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
 * @package    local_time_tracking
 * @copyright  2020 NYIAJ LLC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_time_tracking\form;

use local_time_tracking\local\tracker;
use local_time_tracking\local_time_tracking\settings_provider\settings_provider;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * Course copy form class.
 *
 * @package    local_time_tracking
 * @copyright  2020 NYIAJ LLC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class configure_form extends \moodleform {

    /**
     * Form definition.
     */
    protected function definition()
    {
        $mform = $this->_form;

        $mform->addElement('header', 'generalsettings', get_string('generalsettings', 'local_time_tracking'));

        if (count(tracker::get_settings_providers()) == 1) {
            $mform->addElement('hidden', 'settingsprovider');
            $mform->setDefault('settingsprovider', array_keys(tracker::get_settings_providers())[0]);
        } else {
            $mform->addElement('select', 'settingsprovider', get_string('settingsproviderlabel', 'local_time_tracking'),
                tracker::get_settings_providers());
            $mform->addHelpButton('settingsprovider', 'settingsprovider', 'local_time_tracking');

            $mform->addElement('html', '<hr>');
        }
        $mform->setType('settingsprovider', PARAM_RAW);

        $mform->addElement('advcheckbox', 'trackerenabled', get_string('trackerenabled', 'local_time_tracking'));
        $mform->setType('trackerenabled', PARAM_BOOL);
        $mform->addHelpButton('trackerenabled', 'trackerenabled', 'local_time_tracking');
        $mform->setDefault('trackerenabled', 1);
        $mform->disabledIf('trackerenabled', 'settingsprovider', 'noeq', settings_provider::class);

        $this->input_group($mform, 'interval', 10);
        $this->input_group($mform, 'idlethreshold', 60, 'secondsafternoactivity');
        $this->input_group($mform, 'sessiontimeout', 60 * 10, 'secondsafternoactivity');
        $this->input_group($mform, 'sessiontimeoutwarnthreshold', 60, 'secondsbeforelogout');

        $mform->addElement('select', 'reportdateformat', get_string('reportdateformat', 'local_time_tracking'), [
            'usstandarddate' => userdate(time(), get_string('usstandarddate', 'local_time_tracking')),
            'strftimedate' => userdate(time(), get_string('strftimedate')),
            'strftimedatefullshort' => userdate(time(), get_string('strftimedatefullshort')),
            'strftimedateshort' => userdate(time(), get_string('strftimedateshort')),
            'strftimedatetime' => userdate(time(), get_string('strftimedatetime')),
            'strftimedatetimeshort' => userdate(time(), get_string('strftimedatetimeshort')),
            'strftimedaydate' => userdate(time(), get_string('strftimedaydate')),
            'strftimedaydatetime' => userdate(time(), get_string('strftimedaydatetime')),
            'strftimedayshort' => userdate(time(), get_string('strftimedayshort')),
            'strftimedaytime' => userdate(time(), get_string('strftimedaytime')),
            'strftimemonthyear' => userdate(time(), get_string('strftimemonthyear')),
            'strftimerecent' => userdate(time(), get_string('strftimerecent')),
            'strftimerecentfull' => userdate(time(), get_string('strftimerecentfull')),
        ]);
        $mform->setType('reportdateformat', PARAM_TEXT);
        $mform->addHelpButton('reportdateformat', 'reportdateformat', 'local_time_tracking');

        $this->add_action_buttons();
    }

    private function input_group($mform, $name, $defaultvalue, $secondarylabel = 'seconds') {
        $group = [];
        $group[] = $mform->createElement('text', $name, get_string($name, 'local_time_tracking'), ['size' => 4]);
        $group[] = $mform->createElement('html', get_string($secondarylabel, 'local_time_tracking'));
        $mform->addGroup($group, $name . 'group', get_string($name, 'local_time_tracking'), null, false);
        $mform->setType($name, PARAM_INT);
        $mform->addHelpButton($name . 'group', $name, 'local_time_tracking');
        $mform->setDefault($name, $defaultvalue);
        $mform->disabledIf($name, 'settingsprovider', 'noeq', settings_provider::class);
        $mform->disabledIf($name, 'trackerenabled');
    }
}