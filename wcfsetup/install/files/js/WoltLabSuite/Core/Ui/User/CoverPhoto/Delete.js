/**
 * Deletes the current user cover photo.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/User/CoverPhoto/Delete
 */
define(['Ajax', 'EventHandler', 'Language', 'Ui/Confirmation', 'Ui/Notification'], function (Ajax, EventHandler, Language, UiConfirmation, UiNotification) {
	"use strict";
	
	var _button;
	
	/**
	 * @exports     WoltLabSuite/Core/Ui/User/CoverPhoto/Delete
	 */
	return {
		/**
		 * Initializes the delete handler and enables the delete button on upload.
		 */
		init: function () {
			_button = elBySel('.jsButtonDeleteCoverPhoto');
			_button.addEventListener(WCF_CLICK_EVENT, this._click.bind(this));
			
			EventHandler.add('com.woltlab.wcf.user', 'coverPhoto', function (data) {
				if (typeof data.url === 'string' && data.url.length > 0) {
					elShow(_button.parentNode);
				}
			});
		},
		
		/**
		 * Handles clicks on the delete button.
		 * 
		 * @protected
		 */
		_click: function () {
			UiConfirmation.show({
				confirm: Ajax.api.bind(Ajax, this),
				message: Language.get('wcf.user.coverPhoto.delete.confirmMessage')
			});
		},
		
		_ajaxSuccess: function (data) {
			elBySel('.userProfileCoverPhoto').style.setProperty('background-image', 'url(' + data.returnValues.url + ')', '');
			
			elHide(_button.parentNode);
			
			UiNotification.show();
		},
		
		_ajaxSetup: function () {
			return {
				data: {
					actionName: 'deleteCoverPhoto',
					className: 'wcf\\data\\user\\UserProfileAction'
				}
			};
		}
	};
});
