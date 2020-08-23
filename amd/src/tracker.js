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

define(['jquery', 'local_time_tracking/timer', 'local_time_tracking/inactivity_timer', 'core/ajax', 'core/log'], function($, Timer, InactivityTimer, Ajax, Log) {

    let Tracker = function(sessionId) {
        this.timer = null;
        this.inactivityTimer = null;
        this.sessionId = sessionId;
    };

    /**
     * Add elapsed time to user session.
     *
     * @param {int} elapsedTime
     * @returns {Promise}
     */
    Tracker.prototype.addElapsedTime = function(elapsedTime) {
        return Ajax.call([{
            methodname: 'local_time_tracking_add_elapsed_time', args: {
                sessionid: this.sessionId,
                elapsedtime: elapsedTime
            }
        }])[0];
    };

    Tracker.prototype.start = function() {

        Log.debug('TIME_TRACKER: Init');

        const interval = 3;

        this.timer = new Timer(1000);
        this.timer.start();

        $(this.timer).on('timer.tick', () => {
            const ticks = this.timer.getTicks();

            Log.debug('TRACKER: Tick');

            if (ticks % interval === 0) {
                Log.debug('TRACKER: Add elapsed time ' + interval);
                this.addElapsedTime(interval);
            }
        });

        this.inactivityTimer = new InactivityTimer(5000, 15, 5);
        this.inactivityTimer.init();

        // When user goes inactive, stop tracking time.
        $(this.inactivityTimer).on('inactive', () => {
            this.timer.pause();
        });

        // When a user becomes active, continue tracking time.
        $(this.inactivityTimer).on('active', () => {
            this.timer.start();
        });

        // If user is inactive for too long log them out.
        $(this.inactivityTimer).on('session_timeout', () => {
            this.inactivityTimer.logout();
        });
    };

    return Tracker;
});