/**
 * Provides an object oriented API on top of `setInterval`.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Timer/Repeating
 */
define([], function() {
	"use strict";
	
	/**
	 * Creates a new timer that executes the given `callback` every `delta` milliseconds.
	 * It will be created in started mode. Call `stop()` if necessary.
	 * The `callback` will be passed the owning instance of `Repeating`.
	 * 
	 * @constructor
	 * @param	{function(Repeating)}	callback
	 * @param	{int}			delta
	 */
	function Repeating(callback, delta) {
		if (typeof callback !== 'function') {
			throw new TypeError("Expected a valid callback as first argument.");
		}
		if (delta < 0 || delta > 86400 * 1000) {
			throw new RangeError("Invalid delta " + delta + ". Delta must be in the interval [0, 86400000].");
		}
		
		// curry callback with `this` as the first parameter
		this._callback = callback.bind(undefined, this);
		
		this._delta = delta;
		this._timer = undefined;
		
		this.restart();
	}
	Repeating.prototype = {
		/**
		 * Stops the timer and restarts it. The next call will occur in `delta` milliseconds.
		 */
		restart: function() {
			this.stop();
			
			this._timer = setInterval(this._callback, this._delta);
		},
		
		/**
		 * Stops the timer. It will no longer be called until you call `restart`.
		 */
		stop: function() {
			if (this._timer !== undefined) {
				clearInterval(this._timer);
				this._timer = undefined;
			}
		},
		
		/**
		 * Changes the `delta` of the timer and `restart`s it.
		 * 
		 * @param	{int}	delta	New delta of the timer.
		 */
		setDelta: function(delta) {
			this._delta = delta;
			
			this.restart();
		}
	};
	
	return Repeating;
});
