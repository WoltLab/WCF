/**
 * Handles uploading the style favicon.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Ui/Style/Favicon/Upload
 */
define(['Core', 'Dom/Traverse', 'Language', 'Ui/Notification', 'Upload'], function(Core, DomTraverse, Language, UiNotification, Upload) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function AcpUiStyleImageUpload(styleId) {
		this._styleId = ~~styleId;
		
		Upload.call(this, 'uploadFavicon', 'faviconImage', {
			action: 'uploadFavicon',
			className: 'wcf\\data\\style\\StyleAction'
		});
	}
	Core.inherit(AcpUiStyleImageUpload, Upload, {
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
				elAttr(this._target, 'src', data.returnValues.url + '?timestamp=' + Date.now());
				
				UiNotification.show();
			}
			else if (data.returnValues.errorType) {
				errorMessage = Language.get('wcf.acp.style.favicon.error.' + data.returnValues.errorType);
			}
			
			elInnerError(this._button, errorMessage);
		}
	});
	
	return AcpUiStyleImageUpload;
});
