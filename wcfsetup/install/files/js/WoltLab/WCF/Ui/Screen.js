/**
 * Provides consistent support for media queries and body scrolling.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/Screen
 */
define(['Core', 'Dictionary'], function(Core, Dictionary) {
	"use strict";
	
	var _bodyOverflow = '';
	var _mql = new Dictionary();
	var _scrollDisableCounter = 0;
	
	/**
	 * @exports     WoltLab/WCF/Ui/Screen
	 */
	return {
		/**
		 * Registers event listeners for media query match/unmatch.
		 * 
		 * The `callbacks` object may contain the following keys:
		 *  - `small` or `match`, triggered when media query matches
		 *  - `large` or `unmatch`, triggered when media query no longer matches
		 *  - `setup`, invoked when media query first matches
		 * 
		 * The `small` and `large` keys only exist to increase readability when omitting
		 * the `query` argument and thus default to match the default value of `query`.
		 * 
		 * `query` will default to `(max-width: 767px)`, it allows any value that can
		 * be evaluated with `window.matchMedia`.
		 * 
		 * Returns a UUID that is used to internal identify the callbacks, can be used
		 * to remove binding by calling the `remove` method.
		 * 
		 * @param       {object}        callbacks
		 * @param       {string=}       query
		 * @return      {string}        UUID for listener removal
		 */
		on: function(callbacks, query) {
			var uuid = Core.getUuid(), queryObject = this._getQueryObject(query);
			
			if (typeof callbacks.small === 'function' || typeof callbacks.match === 'function') {
				queryObject.callbacksMatch.set(uuid, callbacks.small || callbacks.match);
			}
			
			if (typeof callbacks.large === 'function' || typeof callbacks.unmatch === 'function') {
				queryObject.callbacksUnmatch.set(uuid, callbacks.large || callbacks.unmatch);
			}
			
			if (typeof callbacks.setup === 'function') {
				if (queryObject.mql.matches) {
					callbacks.setup();
				}
				else {
					queryObject.callbacksSetup.set(uuid, callbacks.setup);
				}
			}
			
			return uuid;
		},
		
		/**
		 * Removes all listeners identified by their common UUID.
		 * 
		 * @param       {string}        uuid    UUID received when calling `on()`
		 * @param       {string=}       query   must match the `query` argument used when calling `on()`
		 */
		remove: function(uuid, query) {
			var queryObject = this._getQueryObject(query);
			
			queryObject.callbacksMatch.delete(uuid);
			queryObject.callbacksUnmatch.delete(uuid);
			queryObject.callbacksSetup.delete(uuid);
		},
		
		/**
		 * Returns a boolean value if a media query expression currently matches.
		 * 
		 * @param       {string=}       query   CSS media query
		 * @returns     {boolean}       true if query matches
		 */
		is: function(query) {
			var queryObject = this._getQueryObject(query);
			
			if (query === 'large') {
				// the query matches for max-width, we need to inverse the logic here
				return !queryObject.mql.matches;
			}
			
			return queryObject.mql.matches;
		},
		
		/**
		 * Disables scrolling of body element.
		 */
		scrollDisable: function() {
			if (_scrollDisableCounter === 0) {
				_bodyOverflow = document.body.style.getPropertyValue('overflow');
				
				document.body.style.setProperty('overflow', 'hidden', '');
			}
			
			_scrollDisableCounter++;
		},
		
		/**
		 * Re-enables scrolling of body element.
		 */
		scrollEnable: function() {
			if (_scrollDisableCounter) {
				_scrollDisableCounter--;
				
				if (_scrollDisableCounter === 0) {
					if (_bodyOverflow) {
						document.body.style.setProperty('overflow', _bodyOverflow, '');
					}
					else {
						document.body.style.removeProperty('overflow');
					}
				}
			}
		},
		
		/**
		 * 
		 * @param       {string=}       query   CSS media query
		 * @return      {Object}        object containing callbacks and MediaQueryList
		 * @protected
		 */
		_getQueryObject: function(query) {
			if (typeof query !== 'string') query = '';
			if (query === '' || query === 'small' || query === 'large') {
				query = '(max-width: 767px)';
			}
			
			var queryObject = _mql.get(query);
			if (!queryObject) {
				queryObject = {
					callbacksMatch: new Dictionary(),
					callbacksUnmatch: new Dictionary(),
					callbacksSetup: new Dictionary(),
					mql: window.matchMedia(query)
				};
				queryObject.mql.addListener(this._mqlChange.bind(this));
				
				_mql.set(query, queryObject);
			}
			
			return queryObject;
		},
		
		/**
		 * Triggered whenever a registered media query now matches or no longer matches.
		 * 
		 * @param       {Event} event   event object
		 * @protected
		 */
		_mqlChange: function(event) {
			var queryObject = this._getQueryObject(event.media);
			if (event.matches) {
				if (queryObject.callbacksSetup.size) {
					queryObject.callbacksSetup.forEach(function(callback) {
						callback();
					});
					
					// discard all setup callbacks after execution
					queryObject.callbacksSetup = new Dictionary();
				}
				
				queryObject.callbacksMatch.forEach(function(callback) {
					callback();
				});
			}
			else {
				queryObject.callbacksUnmatch.forEach(function(callback) {
					callback();
				});
			}
		}
	};
});
