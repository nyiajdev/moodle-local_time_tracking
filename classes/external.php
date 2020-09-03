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
 * Time tracking web services.
 *
 * @package    local_time_tracking
 * @copyright  2020 NYIAJ LLC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_time_tracking;

use coding_exception;
use core\invalid_persistent_exception;
use external_api;
use external_description;
use external_function_parameters;
use invalid_parameter_exception;
use local_time_tracking\persistent\session;
use moodle_exception;
use require_login_exception;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

/**
 * Time tracking web services.
 *
 * @package local_time_tracking
 */
class external extends external_api
{
    /**
     * Returns description of track_module_time() parameters.
     *
     * @return external_function_parameters
     */
    public static function add_elapsed_time_parameters() {
        return new external_function_parameters([
            'sessionid' => new \external_value(PARAM_INT, 'User\'s page session ID.'),
            'elapsedtime' => new \external_value(PARAM_INT, 'Time in seconds to track for user.')
        ]);
    }

    /**
     * Add time to session.
     *
     * @param int $sessionid
     * @param int $elapsedtime
     * @return null
     * @throws coding_exception
     * @throws invalid_persistent_exception
     * @throws invalid_parameter_exception
     * @throws require_login_exception
     * @throws moodle_exception
     */
    public static function add_elapsed_time($sessionid, $elapsedtime) {
        global $USER;

        $params = self::validate_parameters(self::add_elapsed_time_parameters(), [
            'sessionid' => $sessionid,
            'elapsedtime' => $elapsedtime
        ]);

        require_login();

        $session = new session($sessionid);

        if (!$session->get('id')) {
            throw new moodle_exception('sessionnotfound', 'local_time_tracking');
        }

        if ($session->get('userid') != $USER->id) {
            throw new moodle_exception('notmysession', 'local_time_tracking');
        }

        $session->set('elapsedtime', $session->get('elapsedtime') + $params['elapsedtime']);
        $session->update();

        return null;
    }

    /**
     * Returns description of track_module_time() result value.
     *
     * @return external_description
     */
    public static function add_elapsed_time_returns() {
        return null;
    }
}