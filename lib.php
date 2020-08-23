<?php

function local_time_tracking_extend_navigation(global_navigation $nav) {
    global $PAGE, $USER;

    if (is_siteadmin() || AJAX_SCRIPT || CLI_SCRIPT || WS_SERVER) {
        return;
    }

    try {
        if (get_config('local_time_tracking', 'trackerenabled') && $PAGE->context) {
            $tracker = new \local_time_tracking\local\tracker($PAGE->context, $USER->id);
            $tracker->start_session();
        }
    } catch (moodle_exception $e) {
        // Catch any exceptions to prevent page from breaking for users.
        debugging($e->getMessage());
    }
}

/**
 * This function extends the course navigation.
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 * @throws moodle_exception
 */
function local_time_tracking_extend_navigation_course($navigation, $course, $context) {
    if ($node = $navigation->get('coursereports')) {
        $url = new moodle_url('/local/time_tracking/coursereport.php', ['courseid' => $course->id]);
        $node->add(get_string('pluginname', 'local_time_tracking'), $url, navigation_node::TYPE_SETTING, null, null,
            new pix_icon('i/report', ''));
    }
}

/**
 * Format date based on format defined in settings.
 *
 * @param int $timestamp
 * @return string
 * @throws coding_exception
 * @throws dml_exception
 */
function local_time_tracking_format_date(int $timestamp) {
    if ($format = get_config('local_time_tracking', 'reportdateformat')) {
        $component = strpos($format, 'strf') === 0 ? '' : 'local_time_tracking';
    } else {
        $format = 'usstandarddate';
        $component = 'local_time_tracking';
    }

    return userdate($timestamp, get_string($format, $component));
}