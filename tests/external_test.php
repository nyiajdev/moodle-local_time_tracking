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

use local_time_tracking\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once("$CFG->dirroot/webservice/tests/helpers.php");

/**
 * Class external_test
 *
 * @group time_tracking
 */
class local_time_tracking_external_test extends externallib_advanced_testcase {

    private $course;
    private $user;

    public function setUp() {
        $this->course = $this->getDataGenerator()->create_course();
        $this->user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($this->user->id, $this->course->id);

        parent::setUp();
    }

    public function tearDown() {
        $this->course = null;
        $this->user = null;
    }

    public function test_add_elapsed_time() {
        $this->resetAfterTest();

        $this->setUser($this->user);

        $session = new \local_time_tracking\persistent\session(0, (object)[
            'userid' => $this->user->id,
            'contextid' => context_course::instance($this->course->id)->id
        ]);
        $session->create();

        $this->assertEquals($session->get('elapsedtime'), 0);
        external::add_elapsed_time($session->get('id'), 15);
        $session->read();
        $this->assertEquals(15, $session->get('elapsedtime'));
        external::add_elapsed_time($session->get('id'), 15);
        $session->read();
        $this->assertEquals(30, $session->get('elapsedtime'));
    }
}