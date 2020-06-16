/**
 * Handles uploading style preview images.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Ui/Style/Image/Upload
 */
define(['Core', 'Dom/Traverse', 'Language', 'Ui/Notification', 'Upload'], function(Core, DomTraverse, Language, UiNotification, Upload) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function AcpUiStyleImageUpload(styleId, tmpHash, is2x) {
		this._is2x = (is2x === true);
		this._styleId = ~~styleId;
		this._tmpHash = tmpHash;
		
		Upload.call(this, 'uploadImage' + (this._is2x ? '2x' : ''), 'styleImage' + (this._is2x ? '2x' : ''), {
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
				is2x: this._is2x,
				styleId: this._styleId,
				tmpHash: this._tmpHash
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
				errorMessage = Language.get('wcf.acp.style.image.error.' + data.returnValues.errorType);
			}
			
			elInnerError(this._button, errorMessage);
		}
	});
	
	return AcpUiStyleImageUpload;
});
