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


define(['local_time_tracking/tracker', 'core/log'], function(Tracker, Log) {
    return {
        initTracker: function(sessionId) {
            const tracker = new Tracker(sessionId);
            Log.debug('TRACKER: Tracker is enabled and recording user time spent on this page.');
            Log.debug('TRACKER: Starting tracker for session ' + sessionId);
            tracker.start();
        }
    };
});