/**
 * Handles uploading the style's cover photo.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Ui/Style/CoverPhoto/Upload
 */
define(['Core', 'Dom/Traverse', 'Language', 'Ui/Notification', 'Upload'], function(Core, DomTraverse, Language, UiNotification, Upload) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function AcpUiStyleCoverPhotoUpload(styleId) {
		this._styleId = ~~styleId;
		
		Upload.call(this, 'uploadCoverPhoto', 'coverPhotoPreview', {
			action: 'uploadCoverPhoto',
			className: 'wcf\\data\\style\\StyleAction'
		});
	}
	Core.inherit(AcpUiStyleCoverPhotoUpload, Upload, {
		/**
		 * @see	WoltLabSuite/Core/Upload#_createFileElement
		 */
		_createFileElement: function(file) {
			return this._target;
		},
		
		/**
		 * @see	WoltLabSuite/Core/Upload#_getParameters
		 */
		_getParameters: function() {
			return {
				styleID: this._styleId
			};
		},
		
		/**
		 * @see	WoltLabSuite/Core/Upload#_success
		 */
		_success: function(uploadId, data) {
			var errorMessage = '';
			if (data.returnValues.url) {
				this._target.style.setProperty('background-image', 'url(' + data.returnValues.url + '?timestamp=' + Date.now() + ')', '');
				
				UiNotification.show();
			}
			else if (data.returnValues.errorType) {
				errorMessage = Language.get('wcf.user.coverPhoto.upload.error.' + data.returnValues.errorType);
			}
			
			elInnerError(this._button, errorMessage);
		}
	});
	
	return AcpUiStyleCoverPhotoUpload;
});
