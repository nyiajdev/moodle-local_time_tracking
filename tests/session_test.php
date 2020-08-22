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
 * Class session tests.
 *
 * @group time_tracking
 */
class local_time_tracking_session_test extends advanced_testcase {

    private $course;
    private $user;

    public function setUp()
    {
        $this->course = $this->getDataGenerator()->create_course();
        $this->user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($this->user->id, $this->course->id);

        parent::setUp();
    }

    public function tearDown()
    {
        $this->course = null;
        $this->user = null;
    }

    public function test_add_elapsed_time() {
        $this->resetAfterTest();

        $forum = $this->getDataGenerator()->create_module('forum', ['course' => $this->course->id]);

        $coursecontext = context_course::instance($this->course->id);
        $modulecontext = context_module::instance($forum->cmid);

        $session1 = \local_time_tracking\persistent\session::create_from_context($coursecontext, $this->user->id);
        $session2 = \local_time_tracking\persistent\session::create_from_context($modulecontext, $this->user->id);

        $this->assertEquals($coursecontext->id, $session1->get('contextid'));
        $this->assertEquals($this->user->id, $session1->get('userid'));
        $this->assertEquals($this->course->id, $session1->get('relatedcourseid'));
        $this->assertEquals(0, $session1->get('elapsedtime'));

        $this->assertEquals($modulecontext->id, $session2->get('contextid'));
        $this->assertEquals($this->user->id, $session2->get('userid'));
        $this->assertEquals($this->course->id, $session2->get('relatedcourseid'));
        $this->assertEquals(0, $session2->get('elapsedtime'));
    }
}