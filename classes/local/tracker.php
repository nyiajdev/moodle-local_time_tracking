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
 * Track user session with JS.
 *
 * @package    local_time_tracking
 * @copyright  2020 NYIAJ LLC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_time_tracking\local;

use coding_exception;
use context;
use core\invalid_persistent_exception;
use dml_exception;
use local_time_tracking\local_time_tracking\settings_provider\base_settings_provider;
use local_time_tracking\local_time_tracking\settings_provider\settings_provider;
use local_time_tracking\persistent\session;

defined('MOODLE_INTERNAL') || die();

/**
 * Track user session with JS.
 *
 * @package local_time_tracking
 * @copyright  2020 NYIAJ LLC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tracker {

    /**
     * @var context
     */
    private $context;

    /**
     * @var int
     */
    private $userid;

    /**
     * Build a new tracker.
     *
     * @param context $context
     * @param int $userid
     */
    public function __construct(context $context, int $userid) {
        $this->context = $context;
        $this->userid = $userid;
    }

    /**
     * Start a new user session.
     *
     * @throws coding_exception
     * @throws invalid_persistent_exception
     * @throws dml_exception
     */
    public function start_session() {
        global $PAGE;

        $session = \local_time_tracking\persistent\session::create_from_context($this->context, $this->userid);
        $settings = self::get_settings_provider();

        $PAGE->requires->js_call_amd('local_time_tracking/init', 'initTracker', [
            'sessionid' => $session->get('id'),
            'settings' => [
                'interval' => $settings->get_interval(),
                'idlethreshold' => $settings->get_idle_threshold(),
                'sessiontimeout' => $settings->get_session_timeout(),
                'sessiontimeoutwarnthreshold' => $settings->get_session_timeout_warn_threshold()
            ]
        ]);
    }

    /**
     * Get time tracking settings from provider.
     *
     * @return settings_provider
     * @throws dml_exception
     */
    public static function get_settings_provider():  settings_provider {
        if ($class = get_config('local_time_tracking', 'settingsprovider')) {
            return new $class();
        }

        return new settings_provider();
    }

    /**
     * Get sources of the report in all plugins.
     *
     * @return array
     */
    public static function get_settings_providers(): array {
        $providers = [];

        $datasources = \core_component::get_component_classes_in_namespace(null, 'local_time_tracking\\settings_provider');
        foreach ($datasources as $class => $path) {
            if (is_subclass_of($class, base_settings_provider::class)) {
                $providers[$class] = call_user_func([$class, 'get_name']);
            }
        }

        return $providers;
    }
}