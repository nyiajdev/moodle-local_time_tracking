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

define(['jquery'], function($) {

    let Timer = function(interval, limit) {
        if (!interval) {
            interval = 1000;
        }
        if (!limit) {
            limit = 0;
        }
        this.interval = interval;
        this.limit = limit;
        this.internalTimer = null;
        this.ticks = 0;
        this.done = false;
    };

    Timer.prototype.start = function() {
        let self = this;
        self.done = false;
        $(this).trigger('timer.start');
        // First clear out existing interval if it's already running.
        if (this.internalTimer) {
            clearInterval(this.internalTimer);
        }
        this.internalTimer = setInterval(function() {
            self.tick();
        }, self.interval);
    };

    Timer.prototype.pause = function() {
        $(this).trigger('timer.pause');
        clearInterval(this.internalTimer);
        $(this).trigger('timer.paused');
    };

    Timer.prototype.stop = function() {
        $(this).trigger('timer.stop');
        clearInterval(this.internalTimer);
        this.ticks = 0;
        $(this).trigger('timer.stopped');
    };

    Timer.prototype.tick = function() {
        this.ticks++;
        $(this).trigger('timer.tick');
        if (this.limit > 0 && this.ticks >= this.limit) {
            this.done = true;
            this.stop();
            $(this).trigger('timer.done');
        }
    };

    Timer.prototype.getTicks = function() {
        return this.ticks;
    };

    Timer.prototype.getRemainingTicks = function() {
        let remain = this.limit - this.ticks;
        if (remain < 0) {
            return 0;
        }
        return remain;
    };

    Timer.prototype.isCompleted = function() {
        return this.done;
    };

    return Timer;
});
