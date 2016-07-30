/**
 * Handles uploading style preview images.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Acp/Ui/Style/Image/Upload
 */
define(['Core', 'Dom/Traverse', 'Language', 'Ui/Notification', 'Upload'], function(Core, DomTraverse, Language, UiNotification, Upload) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function AcpUiStyleImageUpload(styleId, tmpHash) {
		this._styleId = ~~styleId;
		this._tmpHash = tmpHash;
		
		Upload.call(this, 'uploadImage', 'styleImage', {
			className: 'wcf\\data\\style\\StyleAction'
		});
	}
	Core.inherit(AcpUiStyleImageUpload, Upload, {
		/**
		 * @see	WoltLab/WCF/Upload#_createFileElement
		 */
		_createFileElement: function(file) {
			return this._target;
		},
		
		/**
		 * @see	WoltLab/WCF/Upload#_getParameters
		 */
		_getParameters: function() {
			return {
				styleId: this._styleId,
				tmpHash: this._tmpHash
			};
		},
		
		/**
		 * @see	WoltLab/WCF/Upload#_success
		 */
		_success: function(uploadId, data) {
			var error = DomTraverse.childByClass(this._button.parentNode, 'innerError');
			if (data.returnValues.url) {
				elAttr(this._target, 'src', data.returnValues.url + '?timestamp=' + Date.now());
				
				if (error) {
					elRemove(error);
				}
				
				UiNotification.show();
			}
			else if (data.returnValues.errorType) {
				if (!error) {
					error = elCreate('small');
					error.className = 'innerError';
					
					this._button.parentNode.appendChild(error);
				}
				
				error.textContent = Language.get('wcf.acp.style.image.error.' + data.returnValues.errorType);
			}
		}
	});
	
	return AcpUiStyleImageUpload;
});
