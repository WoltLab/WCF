/**
 * Provides basic details on the JavaScript environment.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Environment
 */
define([], function() {
	"use strict";
	
	var _browser = 'other';
	var _editor = 'none';
	var _platform = 'desktop';
	var _touch = false;
	
	/**
	 * @exports	WoltLab/WCF/Enviroment
	 */
	return {
		/**
		 * Determines environment variables.
		 */
		setup: function() {
			if (typeof window.chrome === 'object') {
				// this detects Opera as well, we could check for window.opr if we need to
				_browser = 'chrome';
			}
			else {
				var styles = window.getComputedStyle(document.documentElement);
				for (var i = 0, length = styles.length; i < length; i++) {
					var property = styles[i];
					
					if (property.indexOf('-ms-') === 0) {
						// it is tempting to use 'msie', but it wouldn't really represent 'Edge'
						_browser = 'microsoft';
					}
					else if (property.indexOf('-moz-') === 0) {
						_browser = 'firefox';
					}
					else if (property.indexOf('-webkit-') === 0) {
						_browser = 'safari';
					}
				}
			}
			
			var ua = window.navigator.userAgent.toLowerCase();
			if (ua.indexOf('crios') !== -1) {
				_browser = 'chrome';
				_platform = 'ios';
			}
			else if (/(?:iphone|ipad|ipod)/.test(ua)) {
				_browser = 'safari';
				_platform = 'ios';
			}
			else if (ua.indexOf('android') !== -1) {
				_platform = 'android';
			}
			else if (ua.indexOf('iemobile') !== -1) {
				_browser = 'microsoft';
				_platform = 'windows';
			}
			
			if (_platform === 'desktop' && (ua.indexOf('mobile') !== -1 || ua.indexOf('tablet') !== -1)) {
				_platform = 'mobile';
			}
			
			_editor = 'redactor';
			_touch = (!!('ontouchstart' in window) || (!!('msMaxTouchPoints' in window.navigator) && window.navigator.msMaxTouchPoints > 0) || window.DocumentTouch && document instanceof DocumentTouch);
		},
		
		/**
		 * Returns the lower-case browser identifier.
		 * 
		 * Possible values:
		 *  - chrome: Chrome and Opera
		 *  - firefox
		 *  - microsoft: Internet Explorer and Microsoft Edge
		 *  - safari
		 * 
		 * @return	{string}	browser identifier
		 */
		browser: function() {
			return _browser;
		},
		
		/**
		 * Returns the available editor's name or an empty string.
		 * 
		 * @return	{string}	editor name
		 */
		editor: function() {
			return _editor;
		},
		
		/**
		 * Returns the browser platform.
		 * 
		 * Possible values:
		 *  - desktop
		 *  - android
		 *  - ios: iPhone, iPad and iPod
		 *  - windows: Windows on phones/tablets
		 * 
		 * @return	{string}	browser platform
		 */
		platform: function() {
			return _platform;
		},
		
		/**
		 * Returns true if browser is potentially used with a touchscreen.
		 * 
		 * Warning: Detecting touch is unreliable and should be avoided at all cost.
		 * 
		 * @deprecated	3.0 - exists for backward-compatibility only, will be removed in the future
		 * 
		 * @return	{boolean}	true if a touchscreen is present
		 */
		touch: function() {
			return _touch;
		}
	};
});
