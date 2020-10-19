/**
 * Uploads replacemnts for media files.
 *
 * @author      Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Media/Upload
 * @since       5.3
 */
define(
	[
		'Core',
		'Dom/ChangeListener',
		'Dom/Util',
		'Language',
		'Ui/Notification',
		'./Upload'
	],
	function(
		Core,
		DomChangeListener,
		DomUtil,
		Language,
		UiNotification,
		MediaUpload
	)
	{
		"use strict";
		
		if (!COMPILER_TARGET_DEFAULT) {
			var Fake = function() {};
			Fake.prototype = {
				_createButton: function() {},
				_success: function() {},
				_upload: function() {},
				_createFileElement: function() {},
				_getParameters: function() {},
				_uploadFiles: function() {},
				_createFileElements: function() {},
				_failure: function() {},
				_insertButton: function() {},
				_progress: function() {},
				_removeButton: function() {}
			};
			return Fake;
		}
		
		/**
		 * @constructor
		 */
		function MediaReplace(mediaID, buttonContainerId, targetId, options) {
			this._mediaID = mediaID;
			
			MediaUpload.call(this, buttonContainerId, targetId, Core.extend(options, {
				action: 'replaceFile'
			}));
		}
		Core.inherit(MediaReplace, MediaUpload, {
			/**
			 * @see	WoltLabSuite/Core/Upload#_createButton
			 */
			_createButton: function() {
				MediaUpload.prototype._createButton.call(this);
				
				this._button.classList.add('small');
				
				var span = elBySel('span', this._button);
				span.textContent = Language.get('wcf.media.button.replaceFile');
			},
			
			/**
			 * @see	WoltLabSuite/Core/Upload#_createFileElement
			 */
			_createFileElement: function() {
				return this._target;
			},
			
			/**
			 * @see	WoltLabSuite/Core/Upload#_getFormData
			 */
			_getFormData: function() {
				return {
					objectIDs: [this._mediaID]
				};
			},
			
			/**
			 * @see	WoltLabSuite/Core/Upload#_success
			 */
			_success: function(uploadId, data) {
				var files = this._fileElements[uploadId];
				
				for (var i = 0, length = files.length; i < length; i++) {
					var file = files[i];
					var internalFileId = elData(file, 'internal-file-id');
					var media = data.returnValues.media[internalFileId];
					
					if (media) {
						if (media.isImage) {
							this._target.innerHTML = media.smallThumbnailTag;
						}
						
						elById('mediaFilename').textContent = media.filename;
						elById('mediaFilesize').textContent = media.formattedFilesize;
						if (media.isImage) {
							elById('mediaImageDimensions').textContent = media.imageDimensions;
						}
						elById('mediaUploader').innerHTML = media.userLinkElement;
						
						this._options.mediaEditor.updateData(media);
						
						// Remove existing error messages.
						elInnerError(this._buttonContainer, '');
						
						UiNotification.show();
					}
					else {
						var error = data.returnValues.errors[internalFileId];
						if (!error) {
							error = {
								errorType: 'uploadFailed',
								filename: elData(file, 'filename')
							};
						}
						
						elInnerError(this._buttonContainer, Language.get('wcf.media.upload.error.' + error.errorType, {
							filename: error.filename
						}));
					}
					
					DomChangeListener.trigger();
				}
			},
		});
		
		return MediaReplace;
	}
);
