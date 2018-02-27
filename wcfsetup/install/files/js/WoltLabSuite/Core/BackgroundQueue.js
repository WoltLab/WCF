/**
 * Manages the invocation of the background queue.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/BackgroundQueue
 */
define(['Ajax'], function(Ajax) {
	"use strict";
	
	var _invocations = 0;
	var _isBusy = false;
	var _url = '';
	
	/**
	 * @exports     WoltLabSuite/Core/BackgroundQueue
	 */
	return {
		/**
		 * Sets the url of the background queue perform action.
		 * 
		 * @param       {string}        url     background queue perform url
		 */
		setUrl: function (url) {
			_url = url;
		},
		
		/**
		 * Invokes the background queue.
		 */
		invoke: function () {
			if (_url === '') {
				console.error('The background queue has not been initialized yet.');
				return;
			}
			
			if (_isBusy) return;
			
			_invocations = 0;
			_isBusy = true;
			
			Ajax.api(this);
		},
		
		_ajaxSuccess: function (data) {
			_invocations++;
			
			// invoke the queue up to 5 times in a row
			if (data > 0 && _invocations < 5) {
				window.setTimeout(this.invoke.bind(this), 1000);
			}
			else {
				_isBusy = false;
			}
		},
		
		_ajaxSetup: function () {
			return {
				url: _url,
				ignoreError: true,
				silent: true
			}
		}
	}
});