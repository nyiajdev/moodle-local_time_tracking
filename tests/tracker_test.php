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
 * Test time tracking "tracker".
 *
 * @package    local_time_tracking
 * @copyright  2020 NYIAJ LLC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\invalid_persistent_exception;
use local_time_tracking\local\tracker;
use local_time_tracking\local_time_tracking\settings_provider\settings_provider;

defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * Test time tracking "tracker".
 *
 * @group time_tracking
 * @copyright  2020 NYIAJ LLC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_time_tracking_tracker_test extends advanced_testcase {

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
     * Test starting tracker.
     *
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_persistent_exception
     */
    public function test_tracker() {
        $this->resetAfterTest();

        $tracker = new tracker(context_course::instance($this->course->id), $this->user->id);
        $session = $tracker->start_session();

        $this->assertNotNull($session);
        $this->assertEquals($this->user->id, $session->get('userid'));
        $this->assertEquals($this->course->id, $session->get('relatedcourseid'));
        $this->assertEquals(context_course::instance($this->course->id)->id, $session->get('contextid'));
    }

    /**
     * Test tracker settings.
     *
     * @throws dml_exception
     */
    public function test_settings_provider() {
        $this->resetAfterTest();

        $this->assertInstanceOf(settings_provider::class, tracker::get_settings_provider());

        set_config('settingsprovider', settings_provider::class, 'local_time_tracking');

        $this->assertInstanceOf(settings_provider::class, tracker::get_settings_provider());

        $this->assertNotEmpty(tracker::get_settings_providers());

        $this->assertTrue(tracker::get_settings_provider()->is_enabled());
    }
}