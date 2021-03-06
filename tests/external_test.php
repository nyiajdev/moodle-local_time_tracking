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
 * Test web services.
 *
 * @package    local_time_tracking
 * @copyright  2020 NYIAJ LLC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\invalid_persistent_exception;
use local_time_tracking\external;
use local_time_tracking\persistent\session;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once("$CFG->dirroot/webservice/tests/helpers.php");

/**
 * Test web services.
 *
 * @group time_tracking
 * @copyright  2020 NYIAJ LLC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_time_tracking_external_test extends externallib_advanced_testcase {

    /**
     * @var stdClass
     */
    private $course;

    /**
     * @var stdClass
     */
    private $user;

    /**
     * Create required objects.
     */
    public function setUp() {
        $this->course = $this->getDataGenerator()->create_course();
        $this->user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($this->user->id, $this->course->id);

        parent::setUp();
    }

    /**
     * Delete objects.
     */
    public function tearDown() {
        $this->course = null;
        $this->user = null;
    }

    /**
     * Test adding elapsed time.
     *
     * @throws invalid_persistent_exception
     * @throws coding_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws require_login_exception
     */
    public function test_add_elapsed_time() {
        $this->resetAfterTest();

        $this->setUser($this->user);

        $session = session::create_from_context(context_course::instance($this->course->id), $this->user->id);

        $this->assertEquals($session->get('elapsedtime'), 0);
        external::add_elapsed_time($session->get('id'), 15);
        $session->read();
        $this->assertEquals(15, $session->get('elapsedtime'));
        external::add_elapsed_time($session->get('id'), 15);
        $session->read();
        $this->assertEquals(30, $session->get('elapsedtime'));

        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage('Time tracking session not found');
        external::add_elapsed_time(1234, 100);
    }

    public function test_not_my_session() {
        $this->resetAfterTest();

        $this->setUser($this->user);

        $otheruser = $this->getDataGenerator()->create_user();

        $session1 = session::create_from_context(context_course::instance($this->course->id), $this->user->id);

        $session2 = session::create_from_context(context_course::instance($this->course->id), $otheruser->id);

        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage('Time tracking session is not yours');
        external::add_elapsed_time($session2->get('id'), 123);
    }
}