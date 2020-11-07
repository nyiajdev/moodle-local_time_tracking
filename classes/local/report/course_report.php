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
 * Display enrolled user sessions.
 *
 * @package    local_time_tracking
 * @copyright  2020 NYIAJ LLC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_time_tracking\local\report;

use coding_exception;
use context_course;
use dml_exception;
use local_time_tracking\persistent\session;
use moodle_exception;
use stdClass;
use table_sql;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/local/time_tracking/lib.php');

/**
 * Display enrolled user sessions.
 *
 * @package local_time_tracking
 * @copyright  2020 NYIAJ LLC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_report extends table_sql {

    /**
     * @var int
     */
    private $courseid;

    /**
     * Build a new course report.
     *
     * @param int $courseid
     * @param string $download Data format type. One of csv, xhtml, ods, etc.
     * @throws coding_exception
     */
    public function __construct(int $courseid, string $download) {
        parent::__construct($courseid);

        $this->courseid = $courseid;

        if ($download) {
            raise_memory_limit(MEMORY_EXTRA);
            $this->is_downloading($download, 'time-tracking-report-' . $courseid);
        }

        // Define the headers and columns.
        $headers = [];
        $columns = [];

        $headers[] = get_string('user');
        $columns[] = 'fullname';
        $headers[] = get_string('totaltime', 'local_time_tracking');
        $columns[] = 'elapsedtime';
        $headers[] = get_string('firstaccess');
        $columns[] = 'firstaccess';
        $headers[] = get_string('lastaccess');
        $columns[] = 'lastaccess';
        $headers[] = get_string('activitymodules', 'local_time_tracking');
        $columns[] = 'modulecount';

        $this->define_columns($columns);
        $this->define_headers($headers);

        $this->set_attribute('courseid', $this->courseid);

        // Set help icons.
        $this->define_help_for_headers([
            '1' => new \help_icon('totaltimeincourse', 'local_time_tracking'),
        ]);
    }

    /**
     * Fullname is treated as a special columname in tablelib and should always
     * be treated the same as the fullname of a user.
     * @uses $this->useridfield if the userid field is not expected to be id
     * then you need to override $this->useridfield to point at the correct
     * field for the user id.
     *
     * @param object $row the data from the db containing all fields from the
     *                    users table necessary to construct the full name of the user in
     *                    current language.
     * @return string contents of cell in column 'fullname', for this row.
     */
    public function col_fullname($row) {
        global $DB;

        if (!$user = $DB->get_record('user', ['id' => $row->userid])) {
            return '';
        }

        $name = fullname($user);
        if ($this->download) {
            return $name;
        }

        if ($this->courseid == SITEID) {
            $profileurl = new \moodle_url('/user/profile.php', ['id' => $user->id]);
        } else {
            $profileurl = new \moodle_url('/user/view.php', ['id' => $user->id, 'course' => $this->courseid]);
        }
        return \html_writer::link($profileurl, $name);
    }

    /**
     * Get total elapsed time in course.
     *
     * @param stdClass $row
     * @return string
     * @throws coding_exception
     */
    public function col_elapsedtime($row) {
        if (!$row->elapsedtime) {
            return $this->is_downloading() ? '' :
                '<span class="text-muted"><i>' . get_string('notimetrackedyet', 'local_time_tracking') . '</i></span>';
        }

        return local_time_tracking_format_elapsed_time($row->elapsedtime);
    }

    /**
     * Get first access to any context within course.
     *
     * @param stdClass $row
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function col_firstaccess($row) {
        if ($row->firstaccess > 0) {
            return local_time_tracking_format_date($row->firstaccess);
        }
        return '';
    }

    /**
     * Get last access to any context within course.
     *
     * @param stdClass $row
     * @return string
     * @throws dml_exception
     * @throws coding_exception
     */
    public function col_lastaccess($row) {
        if ($row->lastaccess > 0) {
            return local_time_tracking_format_date($row->lastaccess);
        }
        return '';
    }

    /**
     * Get number of modules that have sessions for user.
     *
     * @param stdClass $row
     * @return string
     * @throws moodle_exception
     * @throws coding_exception
     */
    public function col_modulecount($row) {
        if (empty($row->modulecount) || $row->modulecount == -1) {
            return '';
        }

        if (!$this->is_downloading()) {
            return \html_writer::link(new \moodle_url('/local/time_tracking/modulereport.php', [
                'courseid' => $this->courseid,
                'userid' => $row->userid
            ]), get_string('activitymodulecount', 'local_time_tracking', ['count' => $row->modulecount]));
        } else {
            return $row->modulecount;
        }
    }

    /**
     * Query database.
     *
     * @param int $pagesize
     * @param bool $useinitialsbar
     * @throws dml_exception
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        global $DB;

        list($wsql, $params) = $this->get_sql_where();
        if ($wsql) {
            $wsql = 'AND ' . $wsql;
        }

        $sql = 'SELECT
                u.id AS userid,
                SUM(s.elapsedtime) AS elapsedtime,
                MIN(s.timecreated) AS firstaccess,
                MAX(s.timemodified) AS lastaccess,
                COUNT(DISTINCT s.contextid) -1 AS modulecount
                FROM {user} u
                JOIN {user_enrolments} ue ON ue.userid = u.id
                JOIN {enrol} e ON e.id = ue.enrolid AND e.courseid = :courseid
                LEFT JOIN {' . session::TABLE . '} s ON s.userid = u.id AND s.relatedcourseid = e.courseid
                WHERE 1
                ' . $wsql . '
                GROUP BY u.id, s.relatedcourseid';

        $params['courseid'] = $this->uniqueid;

        $sort = $this->get_sql_sort();
        if ($sort) {
            $sql = $sql . ' ORDER BY ' . $sort;
        }

        if ($pagesize != -1) {
            $countsql = 'SELECT COUNT(DISTINCT u.id)
                         FROM {user} u
                         JOIN {user_enrolments} ue ON ue.userid = u.id
                         JOIN {enrol} e ON e.id = ue.enrolid AND e.courseid = :courseid
                         WHERE 1
                         ' . $wsql;
            $total = $DB->count_records_sql($countsql, $params);
            $this->pagesize($pagesize, $total);
        } else {
            $this->pageable(false);
        }

        if ($useinitialsbar && !$this->is_downloading()) {
            $this->initialbars(true);
        }

        $this->rawdata = $DB->get_recordset_sql($sql, $params, $this->get_page_start(), $this->get_page_size());
    }
}

