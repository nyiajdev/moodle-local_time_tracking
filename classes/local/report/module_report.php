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
 * Display module sessions within a course for a single user.
 *
 * @package    local_time_tracking
 * @copyright  2020 NYIAJ LLC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_time_tracking\local\report;

use cm_info;
use coding_exception;
use context_course;
use dml_exception;
use html_writer;
use local_time_tracking\persistent\session;
use moodle_exception;
use stdClass;
use table_sql;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/local/time_tracking/lib.php');

/**
 * Display module sessions within a course for a single user.
 *
 * @package local_time_tracking
 * @copyright  2020 NYIAJ LLC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class module_report extends table_sql {

    /**
     * @var int
     */
    private $courseid;

    /**
     * @var int
     */
    private $userid;

    /**
     * @var cm_info[]
     */
    private $cms = [];

    /**
     * Build a new course report.
     *
     * @param int $userid
     * @param int $courseid
     * @param string $download Data format type. One of csv, xhtml, ods, etc.
     * @throws moodle_exception
     */
    public function __construct(int $userid, int $courseid, string $download) {
        parent::__construct($userid);

        global $DB;

        $this->attributes['class'] = 'flexible table';

        foreach (get_fast_modinfo($courseid)->get_cms() as $cm) {
            $this->cms[$cm->id] = $cm;
        }

        $this->userid = $userid;
        $this->courseid = $courseid;

        if ($download) {
            $user = $DB->get_record('user', ['id' => $userid]);

            raise_memory_limit(MEMORY_EXTRA);
            $this->is_downloading($download, 'module-time-tracking-report-' . fullname($user));
        }

        // Define the headers and columns.
        $headers = [];
        $columns = [];

        $headers[] = get_string('activitymodule', 'local_time_tracking');
        $columns[] = 'activitymodule';
        $headers[] = get_string('time', 'local_time_tracking');
        $columns[] = 'elapsedtime';
        $headers[] = get_string('firstaccess');
        $columns[] = 'firstaccess';
        $headers[] = get_string('lastaccess');
        $columns[] = 'lastaccess';

        $this->define_columns($columns);
        $this->define_headers($headers);

        $this->no_sorting('activitymodule');
        $this->no_sorting('elapsedtime');
        $this->no_sorting('firstaccess');
        $this->no_sorting('lastaccess');

        $this->set_attribute('courseid', $this->courseid);

        // Set help icons.
        $this->define_help_for_headers([
            '1' => new \help_icon('totaltimeincourse', 'local_time_tracking'),
        ]);
    }

    /**
     * Get activity module name.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_activitymodule($row) {
        $content = '';
        if (isset($this->cms[$row->cmid])) {
            $cm = $this->cms[$row->cmid];
            $content = $cm->get_formatted_name();
        }

        return $content;
    }

    /**
     * Get total elapsed time for module.
     *
     * @param stdClass $row
     * @return string
     * @throws coding_exception
     */
    public function col_elapsedtime($row) {
        $t = $row->elapsedtime;

        if (!$t && !$this->is_downloading()) {
            return '<span class="text-muted"><i>' . get_string('notimetrackedyet', 'local_time_tracking') . '</i></span>';
        }

        return sprintf('%02d:%02d:%02d', ($t / 3600), ($t / 60 % 60), $t % 60);
    }

    /**
     * Get user first time access to activity module.
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
     * Get user last time access to activity module.
     *
     * @param stdClass $row
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function col_lastaccess($row) {
        if ($row->lastaccess > 0) {
            return local_time_tracking_format_date($row->lastaccess);
        }
        return '';
    }

    /**
     * Get when each session was started.
     *
     * @param stdClass $row
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function col_timecreated($row) {
        if ($row->firstaccess > 0) {
            return local_time_tracking_format_date($row->firstaccess);
        }
        return '';
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
                s.*,
                ctx.instanceid AS cmid,
                (SELECT MIN(s2.timecreated) FROM {' . session::TABLE . '} s2 WHERE s2.contextid = s.contextid) AS firstaccess,
                (SELECT MAX(s2.timecreated) FROM {' . session::TABLE . '} s2 WHERE s2.contextid = s.contextid) AS lastaccess,
                (SELECT SUM(s2.elapsedtime) FROM {' . session::TABLE . '} s2 WHERE s2.contextid = s.contextid) AS totalelapsedtime
                FROM {' . session::TABLE . '} s
                JOIN {context} ctx ON ctx.id = s.contextid AND ctx.contextlevel = ' . CONTEXT_MODULE . '
                WHERE s.relatedcourseid = :courseid
                AND s.userid = :userid
                ' . $wsql . '
                ';

        $params['courseid'] = $this->courseid;
        $params['userid'] = $this->userid;

        $sort = $this->get_sql_sort();
        if ($sort) {
            $sql = $sql . ' ORDER BY s.' . $sort;
        }

        if ($pagesize != -1) {
            $countsql = 'SELECT COUNT(DISTINCT s.id)
                         FROM {' . session::TABLE . '} s
                         JOIN {context} ctx ON ctx.id = s.contextid AND ctx.contextlevel = ' . CONTEXT_MODULE . '
                         WHERE s.relatedcourseid = :courseid
                         AND s.userid = :userid
                         ' . $wsql;
            $total = $DB->count_records_sql($countsql, $params);
            $this->pagesize($pagesize, $total);
        } else {
            $this->pageable(false);
        }

        if ($useinitialsbar && !$this->is_downloading()) {
            $this->initialbars(false);
        }

        $this->rawdata = $DB->get_recordset_sql($sql, $params, $this->get_page_start(), $this->get_page_size());
    }

    /**
     * Convenience method to call a number of methods for you to display the table.
     *
     * @param int $pagesize
     * @param bool $useinitialsbar
     * @param string $downloadhelpbutton
     * @throws coding_exception
     * @throws dml_exception
     */
    public function out($pagesize, $useinitialsbar, $downloadhelpbutton = '') {
        global $DB, $OUTPUT;

        if ($this->is_downloading()) {
            return parent::out($pagesize, $useinitialsbar, $downloadhelpbutton);
        }

        if (!$this->columns) {
            $onerow = $DB->get_record_sql("SELECT {$this->sql->fields} FROM {$this->sql->from} WHERE {$this->sql->where}",
                $this->sql->params, IGNORE_MULTIPLE);
            // If columns is not set then define columns as the keys of the rows returned from the db.
            $this->define_columns(array_keys((array)$onerow));
            $this->define_headers(array_keys((array)$onerow));
        }
        $this->pagesize = $pagesize;
        $this->setup();
        $this->query_db($pagesize, $useinitialsbar);
        $this->start_output();
        $groups = [];
        foreach ($this->rawdata as $data) {
            if (!isset($groups[$data->cmid])) {
                $groups[$data->cmid] = $this->format_row($data);
                $groups[$data->cmid]['subrows'] = [];
                $groups[$data->cmid]['uniqueid'] = uniqid();
                $groups[$data->cmid]['totalelapsedtime'] = local_time_tracking_format_elapsed_time($data->totalelapsedtime);
                $groups[$data->cmid]['timecreated'] = local_time_tracking_format_date($data->timecreated);
                $groups[$data->cmid]['timemodified'] = local_time_tracking_format_date($data->timemodified);
            }

            $groups[$data->cmid]['subrows'][] = $this->format_row($data);
        }
        echo '</tbody>'; // Close tbody opened by start_output().
        echo $OUTPUT->render_from_template('local_time_tracking/module_report_table', [
            'groups' => array_values($groups)
        ]);
        echo '<tbody>'; // Open tbody to be immediately closed by finish_output().
        $this->close_recordset();
        $this->finish_output();
    }
}

