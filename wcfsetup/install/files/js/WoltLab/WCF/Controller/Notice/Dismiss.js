/**
 * Handles dismissable user notices.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Controller/Notice/Dismiss
 */
define(['Ajax'], function(Ajax) {
	"use strict";
	
	/**
	 * @exports	WoltLab/WCF/Controller/Notice/Dismiss
	 */
	var ControllerNoticeDismiss = {
		/**
		 * Initializes dismiss buttons.
		 */
		setup: function() {
			var buttons = document.getElementsByClassName('jsDismissNoticeButton');
			
			if (buttons.length) {
				var clickCallback = this._click.bind(this);
				for (var i = 0, length = buttons.length; i < length; i++) {
					buttons[i].addEventListener('click', clickCallback);
				}
			}
		},
		
		/**
		 * Sends a request to dismiss a notice and removes it afterwards.
		 */
		_click: function(event) {
			var button = event.currentTarget;
			
			Ajax.apiOnce({
				data: {
					actionName: 'dismiss',
					className: 'wcf\\data\\notice\\NoticeAction',
					objectIDs: [ button.getAttribute('data-object-id') ]
				},
				success: function() {
					var parent = button.parentNode;
					
					parent.addEventListener('transitionend', function() {
						parent.parentNode.removeChild(parent);
					});
					
					parent.classList.remove('active');
				}
			});
		}
	};
	
	return ControllerNoticeDismiss;
});
