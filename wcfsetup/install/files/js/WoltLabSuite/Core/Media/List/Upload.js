/**
 * Uploads media files.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Media/List/Upload
 */
define(
	[
		'Core', 'Dom/ChangeListener', 'Dom/Traverse', 'Dom/Util', 'Language', 'Ui/Confirmation', 'Ui/Notification', '../Upload'
	],
	function(
		Core, DomChangeListener, DomTraverse, DomUtil, Language, UiConfirmation, UiNotification, MediaUpload
	)
{
	"use strict";
	
	/**
	 * @constructor
	 */
	function MediaListUpload(buttonContainerId, targetId, options) {
		options = options || {};
		
		// only one file may be uploaded file list upload for proper error display
		options.multiple = false;
		
		MediaUpload.call(this, buttonContainerId, targetId, options);
	}
	Core.inherit(MediaListUpload, MediaUpload, {
		/**
		 * Creates the upload button.
		 */
		_createButton: function() {
			this._fileUpload = elCreate('input');
			elAttr(this._fileUpload, 'type', 'file');
			elAttr(this._fileUpload, 'name', this._options.name);
			this._fileUpload.addEventListener('change', this._upload.bind(this));
			
			this._button = elCreate('p');
			this._button.className = 'button uploadButton';
			
			this._button.innerHTML = '<span class="icon icon16 fa-upload"></span> <span>' + Language.get('wcf.global.button.upload') + '</span>';
			
			DomUtil.prepend(this._fileUpload, this._button);
			
			this._insertButton();
			
			DomChangeListener.trigger();
		},
		
		/**
		 * @see	WoltLabSuite/Core/Upload#_success
		 */
		_success: function(uploadId, data) {
			var icon = DomTraverse.childByClass(this._button, 'icon');
			icon.classList.remove('fa-spinner');
			icon.classList.add('fa-upload');
			
			var file = this._fileElements[uploadId][0];
			
			var internalFileId = elData(file, 'internal-file-id');
			var media = data.returnValues.media[internalFileId];
			
			if (media) {
				UiNotification.show(Language.get('wcf.media.upload.success'), function() {
					window.location.reload();
				});
			}
			else {
				var error = data.returnValues.errors[internalFileId];
				if (!error) {
					error = {
						errorType: 'uploadFailed',
						filename: elData(file, 'filename')
					};
				}
				
				UiConfirmation.show({
					confirm: function() {
						// do nothing
					},
					message: Language.get('wcf.media.upload.error.' + error.errorType, {
						filename: error.filename
					})
				});
			}
		},
		
		/**
		 * @see	WoltLabSuite/Core/Upload#_success
		 */
		_upload: function(event, file, blob) {
			var uploadId = MediaListUpload._super.prototype._upload.call(this, event, file, blob);
			
			var icon = DomTraverse.childByClass(this._button, 'icon');
			window.setTimeout(function() {
				icon.classList.remove('fa-upload');
				icon.classList.add('fa-spinner');
			}, 500);
			
			return uploadId;
		}
	});
	
	return MediaListUpload;
});
