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
	
	var _mql = new Dictionary();
	var _scrollDisableCounter = 0;
	
	var _mqMap = Dictionary.fromObject({
		'screen-xs': '(max-width: 544px)',                              /* smartphone */
		'screen-sm': '(min-width: 545px) and (max-width: 768px)',       /* tablet (portrait) */
		'screen-sm-down': '(max-width: 768px)',                         /* smartphone + tablet (portrait) */
		'screen-sm-up': '(min-width: 545px)',                           /* tablet (portrait) + tablet (landscape) + desktop */
		'screen-sm-md': '(min-width: 545px) and (max-width: 1024px)',   /* tablet (portrait) + tablet (landscape) */
		'screen-md': '(min-width: 769px) and (max-width: 1024px)',      /* tablet (landscape) */
		'screen-md-down': '(max-width: 1024px)',                        /* smartphone + tablet (portrait) + tablet (landscape) */
		'screen-md-up': '(min-width: 1024px)',                          /* tablet (landscape) + desktop */
		'screen-lg': '(min-width: 1025px)'                              /* desktop */
	});
	
	/**
	 * @exports     WoltLab/WCF/Ui/Screen
	 */
	return {
		/**
		 * Registers event listeners for media query match/unmatch.
		 * 
		 * The `callbacks` object may contain the following keys:
		 *  - `match`, triggered when media query matches
		 *  - `unmatch`, triggered when media query no longer matches
		 *  - `setup`, invoked when media query first matches
		 * 
		 * Returns a UUID that is used to internal identify the callbacks, can be used
		 * to remove binding by calling the `remove` method.
		 * 
		 * @param       {string}        query           media query
		 * @param       {object}        callbacks       callback functions
		 * @return      {string}        UUID for listener removal
		 */
		on: function(query, callbacks) {
			var uuid = Core.getUuid(), queryObject = this._getQueryObject(query);
			
			if (typeof callbacks.match === 'function') {
				queryObject.callbacksMatch.set(uuid, callbacks.match);
			}
			
			if (typeof callbacks.unmatch === 'function') {
				queryObject.callbacksUnmatch.set(uuid, callbacks.unmatch);
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
		 * @param       {string}        query   must match the `query` argument used when calling `on()`
		 * @param       {string}        uuid    UUID received when calling `on()`
		 */
		remove: function(query, uuid) {
			var queryObject = this._getQueryObject(query);
			
			queryObject.callbacksMatch.delete(uuid);
			queryObject.callbacksUnmatch.delete(uuid);
			queryObject.callbacksSetup.delete(uuid);
		},
		
		/**
		 * Returns a boolean value if a media query expression currently matches.
		 * 
		 * @param       {string}        query   CSS media query
		 * @returns     {boolean}       true if query matches
		 */
		is: function(query) {
			return this._getQueryObject(query).mql.matches;
		},
		
		/**
		 * Disables scrolling of body element.
		 */
		scrollDisable: function() {
			if (_scrollDisableCounter === 0) {
				document.documentElement.classList.add('disableScrolling');
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
					document.documentElement.classList.remove('disableScrolling');
				}
			}
		},
		
		/**
		 * 
		 * @param       {string}        query   CSS media query
		 * @return      {Object}        object containing callbacks and MediaQueryList
		 * @protected
		 */
		_getQueryObject: function(query) {
			if (typeof query !== 'string' || query.trim() === '') {
				throw new TypeError("Expected a non-empty string for parameter 'query'.");
			}
			
			if (_mqMap.has(query)) query = _mqMap.get(query);
			
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
