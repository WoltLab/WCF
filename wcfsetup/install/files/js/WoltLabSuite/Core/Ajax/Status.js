/**
 * Provides the AJAX status overlay.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ajax/Status
 */
define(['Language'], function(Language) {
	"use strict";
	
	var _activeRequests = 0;
	var _overlay = null;
	var _timeoutShow = null;
	
	/**
	 * @exports	WoltLabSuite/Core/Ajax/Status
	 */
	var AjaxStatus = {
		/**
		 * Initializes the status overlay on first usage.
		 */
		_init: function() {
			_overlay = elCreate('div');
			_overlay.classList.add('spinner');
			
			var icon = elCreate('span');
			icon.className = 'icon icon48 fa-spinner';
			_overlay.appendChild(icon);
			
			var title = elCreate('span');
			title.textContent = Language.get('wcf.global.loading');
			_overlay.appendChild(title);
			
			document.body.appendChild(_overlay);
		},
		
		/**
		 * Shows the loading overlay.
		 */
		show: function() {
			if (_overlay === null) {
				this._init();
			}
			
			_activeRequests++;
			
			if (_timeoutShow === null) {
				_timeoutShow = window.setTimeout(function() {
					if (_activeRequests) {
						_overlay.classList.add('active');
					}
					
					_timeoutShow = null;
				}, 250);
			}
		},
		
		/**
		 * Hides the loading overlay.
		 */
		hide: function() {
			_activeRequests--;
			
			if (_activeRequests === 0) {
				if (_timeoutShow !== null) {
					window.clearTimeout(_timeoutShow);
				}
				
				_overlay.classList.remove('active');
			}
		}
	};
	
	return AjaxStatus;
});
