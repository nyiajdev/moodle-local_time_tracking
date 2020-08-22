<?php

function local_time_tracking_extend_navigation(global_navigation $nav) {
    global $PAGE, $USER;

    try {
        if ($PAGE->context) {

            $session = \local_time_tracking\persistent\session::create_from_context($PAGE->context, $USER->id);

            $PAGE->requires->js_call_amd('local_time_tracking/init', 'initTracker', [
                'sessionid' => $session->get('id')
            ]);
        }
    } catch (moodle_exception $e) {
        // Catch any exceptions to prevent page from breaking for users.
        debugging($e->getMessage());
    }
}
