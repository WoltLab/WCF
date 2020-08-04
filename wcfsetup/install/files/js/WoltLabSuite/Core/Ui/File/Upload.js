/**
 * Uploads file via AJAX.
 *
 * @author	Joshua Ruesweg, Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/File/Upload
 * @since	5.2
 */
define(['Core', 'Language', 'Dom/Util', 'WoltLabSuite/Core/Ui/File/Delete', 'Upload'], function(Core, Language, DomUtil, DeleteHandler, CoreUpload) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function Upload(buttonContainerId, targetId, options) {
		options = options || {};
		
		if (options.internalId === undefined) {
			throw new Error("Missing internal id.");
		}
		
		// set default options
		this._options = Core.extend({
			// name if the upload field
			name: '__files[]',
			// is true if every file from a multi-file selection is uploaded in its own request
			singleFileRequests: false,
			// url for uploading file
			url: 'index.php?ajax-file-upload/&t=' + SECURITY_TOKEN,
			// image preview
			imagePreview: false,
			// max files
			maxFiles: null,
			// array of acceptable file types, null if any file type is acceptable
			acceptableFiles: null,
		}, options);
		
		this._options.multiple = this._options.maxFiles === null || this._options.maxFiles > 1; 
		
		if (this._options.url.indexOf('index.php') === 0) {
			this._options.url = WSC_API_URL + this._options.url;
		}
		
		this._buttonContainer = elById(buttonContainerId);
		if (this._buttonContainer === null) {
			throw new Error("Element id '" + buttonContainerId + "' is unknown.");
		}
		
		this._target = elById(targetId);
		if (targetId === null) {
			throw new Error("Element id '" + targetId + "' is unknown.");
		}
		
		if (options.multiple && this._target.nodeName !== 'UL' && this._target.nodeName !== 'OL') {
			throw new Error("Target element has to be list or table body if uploading multiple files is supported.");
		}
		
		this._fileElements = [];
		this._internalFileId = 0;
		
		// upload ids that belong to an upload of multiple files at once
		this._multiFileUploadIds = [];
		
		this._createButton();
		this.checkMaxFiles();
		
		this._deleteHandler = new DeleteHandler(buttonContainerId, targetId, this._options.imagePreview, this);
	}
	
	Core.inherit(Upload, CoreUpload, {
		_createFileElement: function(file) {
			var element = Upload._super.prototype._createFileElement.call(this, file);
			element.classList.add('box64', 'uploadedFile');
			
			var progress = elBySel('progress', element);
			
			var icon = elCreate('span');
			icon.className = 'icon icon64 fa-spinner';
			
			var fileName = element.textContent;
			element.textContent = "";
			element.append(icon);
			
			var innerDiv = elCreate('div');
			var fileNameP = elCreate('p');
			fileNameP.textContent = fileName; // file.name
			
			var smallProgress = elCreate('small');
			smallProgress.appendChild(progress);
			
			innerDiv.appendChild(fileNameP);
			innerDiv.appendChild(smallProgress);
			
			var div = elCreate('div');
			div.appendChild(innerDiv);
			
			var ul = elCreate('ul');
			ul.className = 'buttonGroup';
			div.appendChild(ul);
			
			// reset element textContent and replace with own element style
			element.append(div);
			
			return element;
		},
		
		_failure: function(uploadId, data, responseText, xhr, requestOptions) {
			for (var i = 0, length = this._fileElements[uploadId].length; i < length; i++) {
				this._fileElements[uploadId][i].classList.add('uploadFailed');
				
				elBySel('small', this._fileElements[uploadId][i]).innerHTML = '';
				var icon = elBySel('.icon', this._fileElements[uploadId][i]);
				icon.classList.remove('fa-spinner');
				icon.classList.add('fa-ban');
				
				var innerError = elCreate('span');
				innerError.className = 'innerError';
				innerError.textContent = Language.get('wcf.upload.error.uploadFailed');
				DomUtil.insertAfter(innerError, elBySel('small', this._fileElements[uploadId][i]));
			}
			
			throw new Error("Upload failed: " + data.message);
		},
		
		_upload: function(event, file, blob) {
			var innerError = elBySel('small.innerError:not(.innerFileError)', this._buttonContainer.parentNode);
			if (innerError) elRemove(innerError);
			
			return Upload._super.prototype._upload.call(this, event, file, blob);
		},
		
		_success: function(uploadId, data, responseText, xhr, requestOptions) {
			for (var i = 0, length = this._fileElements[uploadId].length; i < length; i++) {
				if (data['files'][i] !== undefined) {
					if (this._options.imagePreview) {
						if (data['files'][i].image === null) {
							throw new Error("Expect image for uploaded file. None given.");
						}
						
						elRemove(this._fileElements[uploadId][i]);
						
						if (elBySel('img.previewImage', this._target) !== null) {
							elBySel('img.previewImage', this._target).setAttribute('src', data['files'][i].image);
						}
						else {
							var image = elCreate('img');
							image.classList.add('previewImage');
							image.setAttribute('src', data['files'][i].image);
							image.setAttribute('style', "max-width: 100%;");
							elData(image, 'unique-file-id', data['files'][i].uniqueFileId);
							this._target.appendChild(image);
						}
					}
					else {
						elData(this._fileElements[uploadId][i], 'unique-file-id', data['files'][i].uniqueFileId);
						elBySel('small', this._fileElements[uploadId][i]).textContent = data['files'][i].filesize;
						var icon = elBySel('.icon', this._fileElements[uploadId][i]);
						icon.classList.remove('fa-spinner');
						icon.classList.add('fa-' + data['files'][i].icon);
					}
				}
				else if (data['error'][i] !== undefined) {
					this._fileElements[uploadId][i].classList.add('uploadFailed');
					
					elBySel('small', this._fileElements[uploadId][i]).innerHTML = '';
					var icon = elBySel('.icon', this._fileElements[uploadId][i]);
					icon.classList.remove('fa-spinner');
					icon.classList.add('fa-ban');
					
					if (elBySel('.innerError', this._fileElements[uploadId][i]) === null) {
						var innerError = elCreate('span');
						innerError.className = 'innerError';
						innerError.textContent = data['error'][i].errorMessage;
						DomUtil.insertAfter(innerError, elBySel('small', this._fileElements[uploadId][i]));
					}
					else {
						elBySel('.innerError', this._fileElements[uploadId][i]).textContent = data['error'][i].errorMessage;
					}
				}
				else {
					throw new Error('Unknown uploaded file for uploadId ' + uploadId + '.');
				}
			}
			
			// create delete buttons
			this._deleteHandler.rebuild();
			this.checkMaxFiles();
			Core.triggerEvent(this._target, 'change');
		},
		
		_getFormData: function() {
			return {
				internalId: this._options.internalId
			};
		},
		
		validateUpload: function(files) {
			if (this._options.maxFiles === null || files.length + this.countFiles() <= this._options.maxFiles) {
				return true;
			}
			else {
				var innerError = elBySel('small.innerError:not(.innerFileError)', this._buttonContainer.parentNode);
				
				if (innerError === null) {
					innerError = elCreate('small');
					innerError.className = 'innerError';
					DomUtil.insertAfter(innerError, this._buttonContainer);
				}
				
				innerError.textContent = Language.get('wcf.upload.error.reachedRemainingLimit', {
					maxFiles: this._options.maxFiles - this.countFiles()
				});
				
				return false;
			}
		},
		
		/**
		 * Returns the count of the uploaded images.
		 * 
		 * @return {int}
		 */
		countFiles: function() {
			if (this._options.imagePreview) {
				return elBySel('img', this._target) !== null ? 1 : 0;
			}
			else {
				return this._target.childElementCount;
			}
		},
		
		/**
		 * Checks the maximum number of files and enables or disables the upload button.
		 */
		checkMaxFiles: function() {
			if (this._options.maxFiles !== null && this.countFiles() >= this._options.maxFiles) {
				elHide(this._button);
			}
			else {
				elShow(this._button);
			}
		}
	});
	
	return Upload;
});
