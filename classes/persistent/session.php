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
 * Individual user page sessions.
 *
 * @package    local_time_tracking
 * @copyright  2020 NYIAJ LLC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_time_tracking\persistent;

use coding_exception;
use context;
use core\invalid_persistent_exception;
use core\persistent;
use dml_exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Individual user page sessions.
 *
 * @package local_time_tracking
 * @copyright  2020 NYIAJ LLC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class session extends persistent {

    /**
     * Persistent table.
     */
    const TABLE = 'local_time_tracking_session';

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'userid' => [
                'type' => PARAM_INT,
            ],
            'contextid' => [
                'type' => PARAM_INT,
            ],
            'elapsedtime' => [
                'type' => PARAM_INT,
                'default' => 0
            ],
            'relatedcourseid' => [
                'type' => PARAM_INT,
                'default' => 1
            ],
            'ipaddress' => [
                'type' => PARAM_TEXT,
                'default' => '0.0.0.0'
            ]
        ];
    }

    /**
     * Create a new user session based on context.
     *
     * @param context $context
     * @param int $userid
     * @return session
     * @throws coding_exception
     * @throws invalid_persistent_exception
     * @throws dml_exception
     */
    public static function create_from_context(context $context, int $userid): session {
        global $DB;

        if ($coursecontext = $context->get_course_context(false)) {
            $courseid = $coursecontext->instanceid;
        } else {
            $courseid = $DB->get_field('course', 'id', ['format' => 'site']);
        }

        $session = new \local_time_tracking\persistent\session(0, (object)[
            'userid' => $userid,
            'contextid' => $context->id,
            'relatedcourseid' => $courseid,
            'ipaddress' => getremoteaddr()
        ]);
        $session->create();

        return $session;
    }
}