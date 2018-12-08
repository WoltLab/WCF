/**
 * Uploads the user cover photo via AJAX.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/User/CoverPhoto/Upload
 */
define(['Core', 'EventHandler', 'Upload', 'Ui/Notification', 'Ui/Dialog'], function(Core, EventHandler, Upload, UiNotification, UiDialog) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function UiUserCoverPhotoUpload() {
		Upload.call(this, 'coverPhotoUploadButtonContainer', 'coverPhotoUploadPreview', {
			action: 'uploadCoverPhoto',
			className: 'wcf\\data\\user\\UserProfileAction'
		});
	}
	Core.inherit(UiUserCoverPhotoUpload, Upload, {
		/**
		 * @see	WoltLabSuite/Core/Upload#_success
		 */
		_success: function(uploadId, data) {
			// remove or display the error message
			elInnerError(this._button, data.returnValues.errorMessage);
			
			// remove the upload progress
			this._target.innerHTML = '';
			
			if (data.returnValues.url) {
				elBySel('.userProfileCoverPhoto').style.setProperty('background-image', 'url(' + data.returnValues.url + ')', '');
				
				UiDialog.close('userProfileCoverPhotoUpload');
				UiNotification.show();
				
				EventHandler.fire('com.woltlab.wcf.user', 'coverPhoto', {
					url: data.returnValues.url
				});
			}
		}
	});
	
	return UiUserCoverPhotoUpload;
});
