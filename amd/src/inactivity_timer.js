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

define(['jquery', 'core/config', 'core/modal_factory', 'core/log'], function($, Config, ModalFactory, Log) {

    /**
     * @param {int} idleThreshold Milliseconds to elapse when user is considered idle
     * @param {int} sessionTimeout Seconds to elapse of inactivity to logout user.
     * @param {int} sessionTimeoutWarnThreshold Seconds before timeout to warn user.
     * @constructor
     */
    let InactivityTimer = function(idleThreshold, sessionTimeout, sessionTimeoutWarnThreshold) {
        this.idleThreshold = idleThreshold;
        this.sessionTimeout = sessionTimeout;
        this.sessionTimeoutWarnThreshold = sessionTimeoutWarnThreshold;
        this.idleTime = 0;
        this.active = true;
        this.warned = false;
        this.timeoutModal = null;
        this.videos = {};
        this.loggedOut = false;

        console.log("NEW IT", this);
    };

    InactivityTimer.prototype.init = function() {
        $(document).mousemove(() => {
            this.activate();
        });

        $(document).keypress(() => {
            this.activate();
        });

        $(document).scroll(() => {
            this.activate();
        });

        setInterval(() => {
            this.idleTime = this.idleTime + 1000;
            if (this.idleTime >= this.idleThreshold) {
                if (this.active) {
                    Log.debug("INACTIVITY_TIMER: EVENT inactive");
                    $(this).trigger('inactive');
                }
                this.active = false;
            }

            let secondsUntilTimeout = this.sessionTimeout - (this.idleTime / 1000);

            if (this.sessionTimeout > 0 && secondsUntilTimeout <= 0) {
                $(this).trigger('session_timeout');
            }
            if (this.sessionTimeout > 0 && this.sessionTimeoutWarnThreshold > 0) {
                let threshold = this.sessionTimeout - this.sessionTimeoutWarnThreshold;
                if (!this.warned && (this.idleTime / 1000) >= threshold) {
                    $(this).trigger('session_timeout_warn');

                }
            }

            // Check for videos on the page.
            $("video").each((index, video) => {
                if (!this.videos.hasOwnProperty(video.id)) {
                    Log.debug("VIDEO DETECTED:  video.id");
                    Log.debug(video);
                    this.videos[video.id] = video;
                    $(video).on("timeupdate", () => {
                        this.activate();
                    });
                }
            });

            // Always update warn modal text, even if it's hidden.
            this.updateTimeoutWarnModal();
        }, 1000);

        $(this).on('session_timeout_warn', () => {
            // Already warned.
            if (this.warned) {
                return;
            }
            this.warned = true;
            this.updateTimeoutWarnModal();
            this.showTimeoutWarnModal();
        });
    };

    InactivityTimer.prototype.updateTimeoutWarnModal = function() {
        let secondsUntilTimeout = this.sessionTimeout - (this.idleTime / 1000);
        if (secondsUntilTimeout < 0) {
            secondsUntilTimeout = 0;
        }
        if (this.timeoutModal) {
            this.timeoutModal.setBody('You have been inactive and will be logged out in ' + secondsUntilTimeout + ' seconds.');
        }
    };

    InactivityTimer.prototype.showTimeoutWarnModal = function() {
        this.getTimeoutWarnModal((modal) => {
            if (!modal.isVisible()) {
                // Update just in case.
                this.updateTimeoutWarnModal();
                modal.show();
            }
        });
    };

    InactivityTimer.prototype.hideTimeoutWarnModal = function() {
        this.getTimeoutWarnModal((modal) => {
            modal.hide();
        });
    };

    /**
     * Manually change timer to active (not idling).
     */
    InactivityTimer.prototype.activate = function() {
        this.idleTime = 0;
        if (!this.active) {
            $(this).trigger('active');
        }
        if (this.timeoutModal && this.timeoutModal.isVisible()) {
            this.hideTimeoutWarnModal();
        }
        this.active = true;
        this.warned = false;
    };

    /**
     * Get modal popup to warn user their session is going to timeout.
     *
     * @param {Function} callback
     */
    InactivityTimer.prototype.getTimeoutWarnModal = function(callback) {
        if (this.timeoutModal) {
            callback(this.timeoutModal);
            return;
        }

        ModalFactory.create({
            type: ModalFactory.types.DEFAULT,
            title: 'Inactivity'
        }).done((modal) => {
            this.timeoutModal = modal;
            callback(this.timeoutModal);
        });
    };

    /**
     * Log out the user but display a message first.
     *
     * @param {string} message
     */
    InactivityTimer.prototype.logout = function(message) {
        if (this.loggedOut) {
            return;
        }
        if (!message) {
            message = 'You are being logged out due to inactivity. Please login again.';
        }
        this.loggedOut = true;
        alert(message);
        window.location.href = M.cfg.wwwroot + "/login/logout.php?loginpage=1&sesskey=" + M.cfg.sesskey;
    };

    return InactivityTimer;
});
