/**
 * Provides an object oriented API on top of `setInterval`.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    class RepeatingTimer {
        _callback;
        _delta;
        _timer;
        /**
         * Creates a new timer that executes the given `callback` every `delta` milliseconds.
         * It will be created in started mode. Call `stop()` if necessary.
         * The `callback` will be passed the owning instance of `Repeating`.
         */
        constructor(callback, delta) {
            if (typeof callback !== "function") {
                throw new TypeError("Expected a valid callback as first argument.");
            }
            if (delta < 0 || delta > 86_400 * 1_000) {
                throw new RangeError(`Invalid delta ${delta}. Delta must be in the interval [0, 86400000].`);
            }
            // curry callback with `this` as the first parameter
            this._callback = callback.bind(undefined, this);
            this._delta = delta;
            this.restart();
        }
        /**
         * Stops the timer and restarts it. The next call will occur in `delta` milliseconds.
         */
        restart() {
            this.stop();
            this._timer = setInterval(this._callback, this._delta);
        }
        /**
         * Stops the timer. It will no longer be called until you call `restart`.
         */
        stop() {
            if (this._timer !== undefined) {
                clearInterval(this._timer);
                this._timer = undefined;
            }
        }
        /**
         * Changes the `delta` of the timer and `restart`s it.
         */
        setDelta(delta) {
            this._delta = delta;
            this.restart();
        }
    }
    return RepeatingTimer;
});
