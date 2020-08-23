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

defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * Install tests.
 *
 * @group time_tracking
 */
class local_time_tracking_install_test extends advanced_testcase {

    public function test_installation() {
        $this->assertEquals(1, get_config('local_time_tracking', 'trackerenabled'));
        $this->assertEquals(10, get_config('local_time_tracking', 'interval'));
        $this->assertEquals(60, get_config('local_time_tracking', 'idlethreshold'));
        $this->assertEquals(60 * 10, get_config('local_time_tracking', 'sessiontimeout'));
        $this->assertEquals(60, get_config('local_time_tracking', 'sessiontimeoutwarnthreshold'));
    }
}