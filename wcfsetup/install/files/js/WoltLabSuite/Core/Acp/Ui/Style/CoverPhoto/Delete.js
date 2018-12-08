/**
 * Deletes the current style's default cover photo.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Ui/Style/CoverPhoto/Delete
 */
define(['Ajax', 'Language', 'Ui/Confirmation', 'Ui/Notification'], function (Ajax, Language, UiConfirmation, UiNotification) {
	"use strict";
	
	var _button, _styleId;
	
	/**
	 * @exports     WoltLabSuite/Core/Acp/Ui/Style/CoverPhoto/Delete
	 */
	return {
		/**
		 * Initializes the delete handler and enables the delete button on upload.
		 * 
		 * @param       {int}           styleId
		 */
		init: function (styleId) {
			_styleId = styleId;
			
			_button = elBySel('.jsButtonDeleteCoverPhoto');
			_button.addEventListener(WCF_CLICK_EVENT, this._click.bind(this));
		},
		
		/**
		 * Handles clicks on the delete button.
		 * 
		 * @param       {Event}         event
		 * @protected
		 */
		_click: function (event) {
			event.preventDefault();
			
			UiConfirmation.show({
				confirm: Ajax.api.bind(Ajax, this),
				message: Language.get('wcf.acp.style.coverPhoto.delete.confirmMessage')
			});
		},
		
		_ajaxSuccess: function (data) {
			elById('coverPhotoPreview').style.setProperty('background-image', 'url(' + data.returnValues.url + ')', '');
			
			elHide(_button);
			
			UiNotification.show();
		},
		
		_ajaxSetup: function () {
			return {
				data: {
					actionName: 'deleteCoverPhoto',
					className: 'wcf\\data\\style\\StyleAction',
					objectIDs: [_styleId]
				}
			};
		}
	};
});
