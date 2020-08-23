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

namespace local_time_tracking\local_time_tracking\settings_provider;

defined('MOODLE_INTERNAL') || die();

abstract class base_settings_provider {

    public abstract static function get_name(): string;

    public abstract function is_enabled(): bool;

    public abstract function get_interval(): int;

    public abstract function get_idle_threshold(): int;

    public abstract function get_session_timeout(): int;

    public abstract function get_session_timeout_warn_threshold(): int;
}