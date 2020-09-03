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

namespace local_time_tracking\local\report;

use cm_info;
use coding_exception;
use context_course;
use html_writer;
use local_time_tracking\persistent\session;
use table_sql;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

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
     * @param int $courseid
     * @param bool $download
     * @throws coding_exception
     */
    public function __construct(int $userid, int $courseid, bool $download) {
        parent::__construct($userid);

        $this->attributes['class'] = 'flexible table';

        foreach (get_fast_modinfo($courseid)->get_cms() as $cm) {
            $this->cms[$cm->id] = $cm;
        }

        $this->userid = $userid;
        $this->courseid = $courseid;

        if ($download) {
            raise_memory_limit(MEMORY_EXTRA);
            $this->is_downloading($download, 'module-time-tracking-report-' . $userid);
        }

        // Define the headers and columns.
        $headers = [];
        $columns = [];

        $headers[] = get_string('activitymodule', 'local_time_tracking');
        $columns[] = 'activitymodule';
        $headers[] = get_string('totaltime', 'local_time_tracking');
        $columns[] = 'totalelapsedtime';
        $headers[] = get_string('firstaccess');
        $columns[] = 'firstaccess';
        $headers[] = get_string('lastaccess');
        $columns[] = 'lastaccess';

        if (is_siteadmin() && !$this->is_downloading()) {
//            $headers[] = get_string('actions');
//            $columns[] = 'actions';
        }

        $this->define_columns($columns);
        $this->define_headers($headers);

        //$this->no_sorting('state');

        $this->set_attribute('courseid', $this->courseid);

        // Set help icons.
        $this->define_help_for_headers([
            '1' => new \help_icon('totaltimeincourse', 'local_time_tracking'),
        ]);
    }

    public function col_activitymodule($row) {
        $content = '';
        if (isset($this->cms[$row->cmid])) {
            $cm = $this->cms[$row->cmid];
            $content = $cm->get_formatted_name();
        }

        return $content;
    }

    public function col_totalelapsedtime($row) {
        $t = $row->elapsedtime;

        if (!$t && !$this->is_downloading()) {
            return '<span class="text-muted"><i>' . get_string('notimetrackedyet', 'local_time_tracking') . '</i></span>';
        }

        return sprintf('%02d:%02d:%02d', ($t/3600),($t/60%60), $t%60);
    }

    public function col_firstaccess($row) {
        if ($row->firstaccess > 0) {
            return local_time_tracking_format_date($row->firstaccess);
        }
    }

    public function col_lastaccess($row) {
        if ($row->lastaccess > 0) {
            return local_time_tracking_format_date($row->lastaccess);
        }
    }

    public function col_timecreated($row) {
        if ($row->firstaccess > 0) {
            return local_time_tracking_format_date($row->firstaccess);
        }
    }

    /**
     * @param int $pagesize
     * @param bool $useinitialsbar
     * @throws \dml_exception
     */
    function query_db($pagesize, $useinitialsbar = true)
    {
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
            $sql = $sql . ' ORDER BY ' . $sort;
        }

        if ($pagesize != -1) {
            $count_sql = 'SELECT COUNT(DISTINCT s.id) 
                          FROM {' . session::TABLE . '} s
                          JOIN {context} ctx ON ctx.id = s.contextid AND ctx.contextlevel = ' . CONTEXT_MODULE . '
                          WHERE s.relatedcourseid = :courseid
                          AND s.userid = :userid
                          ' . $wsql;
            $total = $DB->count_records_sql($count_sql, $params);
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
     * Convenience method to call a number of methods for you to display the
     * table.
     */
    public function out($pagesize, $useinitialsbar, $downloadhelpbutton = '') {
        global $DB, $OUTPUT;
        if (!$this->columns) {
            $onerow = $DB->get_record_sql("SELECT {$this->sql->fields} FROM {$this->sql->from} WHERE {$this->sql->where}",
                $this->sql->params, IGNORE_MULTIPLE);
            //if columns is not set then define columns as the keys of the rows returned
            //from the db.
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
                $groups[$data->cmid]['elapsedtime'] = local_time_tracking_format_elapsed_time($data->elapsedtime);
                $groups[$data->cmid]['timecreated'] = local_time_tracking_format_date($data->timecreated);
                $groups[$data->cmid]['timemodified'] = local_time_tracking_format_date($data->timemodified);
            }

            $groups[$data->cmid]['subrows'][] = $this->format_row($data);
        }
        echo $OUTPUT->render_from_template('local_time_tracking/module_report_table', [
            'groups' => array_values($groups)
        ]);
        $this->close_recordset();
        $this->finish_output();
    }
}
