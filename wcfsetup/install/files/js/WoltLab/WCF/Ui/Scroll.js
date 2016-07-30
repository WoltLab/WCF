/**
 * Smoothly scrolls to an element while accounting for potential sticky headers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/Scroll
 */
define(['Dom/Util'], function(DomUtil) {
	"use strict";
	
	var _callback = null;
	var _callbackScroll = null;
	var _timeoutScroll = null;
	
	/**
	 * @exports     WoltLab/WCF/Ui/Scroll
	 */
	return {
		/**
		 * Scrolls to target element, optionally invoking the provided callback once scrolling has ended.
		 * 
		 * @param       {Element}       element         target element
		 * @param       {function=}     callback        callback invoked once scrolling has ended
		 */
		element: function(element, callback) {
			if (!(element instanceof Element)) {
				throw new TypeError("Expected a valid DOM element.");
			}
			else if (callback !== undefined && typeof callback !== 'function') {
				throw new TypeError("Expected a valid callback function.");
			}
			else if (!document.body.contains(element)) {
				throw new Error("Element must be part of the visible DOM.");
			}
			else if (_callback !== null) {
				throw new Error("Cannot scroll to element, a concurrent request is running.");
			}
			
			if (callback) {
				_callback = callback;
				
				if (_callbackScroll === null) {
					_callbackScroll = this._onScroll.bind(this);
				}
				
				window.addEventListener('scroll', _callbackScroll);
			}
			
			var y = DomUtil.offset(element).top;
			
			if (y <= 50) {
				y = 0;
			}
			else {
				// add an offset of 50 pixel to account for a sticky header
				y -= 50;
			}
			
			window.scrollTo({
				left: 0,
				top: y,
				behavior: 'smooth'
			});
		},
		
		/**
		 * Monitors scroll event to only execute the callback once scrolling has ended.
		 * 
		 * @protected
		 */
		_onScroll: function() {
			if (_timeoutScroll !== null) window.clearTimeout(_timeoutScroll);
			
			_timeoutScroll = window.setTimeout(function() {
				_callback();
				
				window.removeEventListener('scroll', _callbackScroll);
				_callback = null;
				_timeoutScroll = null;
			}, 100);
		}
	};
});
