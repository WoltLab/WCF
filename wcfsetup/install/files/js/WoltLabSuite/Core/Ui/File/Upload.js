/**
 * Uploads file via AJAX.
 *
 * @author	Joshua Ruesweg, Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/File/Upload
 * @since	3.2
 */
define(['AjaxRequest', 'Core', 'Dom/ChangeListener', 'Language', 'Dom/Util', 'Dom/Traverse', 'WoltLabSuite/Core/Ui/File/Delete'], function(AjaxRequest, Core, DomChangeListener, Language, DomUtil, DomTraverse, DeleteHandler) {
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
			// is true if multiple files can be uploaded at once
			multiple: options.maxFiles > 1,
			// name if the upload field
			name: '__files[]',
			// is true if every file from a multi-file selection is uploaded in its own request
			singleFileRequests: false,
			// url for uploading file
			url: 'index.php?ajax-file-upload/&t=' + SECURITY_TOKEN,
			// image preview
			imagePreview: false
		}, options);
		
		this._options.url = Core.convertLegacyUrl(this._options.url);
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
	
	Upload.prototype = {
		/**
		 * Creates the upload button.
		 */
		_createButton: function() {
			this._fileUpload = elCreate('input');
			elAttr(this._fileUpload, 'type', 'file');
			elAttr(this._fileUpload, 'name', this._options.name);
			if (this._options.multiple) {
				elAttr(this._fileUpload, 'multiple', 'true');
			}
			this._fileUpload.addEventListener('change', this._upload.bind(this));
			
			this._button = elCreate('p');
			this._button.className = 'button uploadButton';
			
			var span = elCreate('span');
			span.textContent = Language.get('wcf.global.button.upload');
			this._button.appendChild(span);
			
			DomUtil.prepend(this._fileUpload, this._button);
			
			this._insertButton();
			
			DomChangeListener.trigger();
		},
		
		/**
		 * Creates the document element for an uploaded file.
		 *
		 * @param	{File}		file		uploaded file
		 * @return	{HTMLElement}
		 */
		_createFileElement: function(file) {
			var li = elCreate('li');
			li.classList = 'box64 uploadedFile';
			
			var span = elCreate('span');
			span.classList = 'icon icon64 fa-spinner';
			li.appendChild(span);
			
			var div = elCreate('div');
			var innerDiv = elCreate('div');
			var p = elCreate('p');
			p.textContent = file.name;
			
			var small = elCreate('small');
			var progress = elCreate('progress');
			elAttr(progress, 'max', 100);
			small.appendChild(progress);
			
			innerDiv.appendChild(p);
			innerDiv.appendChild(small);
			
			var ul = elCreate('ul');
			ul.classList = 'buttonGroup';
			
			div.appendChild(innerDiv);
			div.appendChild(ul);
			li.appendChild(div);
			
			this._target.appendChild(li);
			
			return li;
		},
		
		/**
		 * Creates the document elements for uploaded files.
		 *
		 * @param	{(FileList|Array.<File>)}	files		uploaded files
		 */
		_createFileElements: function(files) {
			if (files.length) {
				var uploadId = this._fileElements.length;
				this._fileElements[uploadId] = [];
				
				for (var i = 0, length = files.length; i < length; i++) {
					var file = files[i];
					var fileElement = this._createFileElement(file);
					
					if (!fileElement.classList.contains('uploadFailed')) {
						elData(fileElement, 'filename', file.name);
						elData(fileElement, 'internal-file-id', this._internalFileId++);
						this._fileElements[uploadId][i] = fileElement;
					}
				}
				
				DomChangeListener.trigger();
				
				return uploadId;
			}
			
			return null;
		},
		
		/**
		 * Handles a failed file upload.
		 *
		 * @param	{int}			uploadId	identifier of a file upload
		 * @param	{object<string, *>}	data		response data
		 * @param	{string}		responseText	response
		 * @param	{XMLHttpRequest}	xhr		request object
		 * @param	{object<string, *>}	requestOptions	options used to send AJAX request
		 * @return	{boolean}	true if the error message should be shown
		 */
		_failure: function(uploadId, data, responseText, xhr, requestOptions) {
			for (var i in this._fileElements[uploadId]) {
				this._fileElements[uploadId][i].classList.add('uploadFailed');
				
				elBySel('small', this._fileElements[uploadId][i]).innerHTML = '';
				elBySel('.icon', this._fileElements[uploadId][i]).classList.remove('fa-spinner');
				elBySel('.icon', this._fileElements[uploadId][i]).classList.add('fa-ban');
				
				var innerError = elCreate('span');
				innerError.classList = 'innerError';
				innerError.textContent = Language.get('wcf.upload.error.uploadFailed');
				DomUtil.insertAfter(innerError, elBySel('small', this._fileElements[uploadId][i]));
			}
			
			throw new Error("Upload failed: "+ data.message);
			
			return false;
		},
		
		/**
		 * Return additional parameters for upload requests.
		 *
		 * @return	{object<string, *>}	additional parameters
		 */
		_getParameters: function() {
			return {};
		},
		
		/**
		 * Inserts the created button to upload files into the button container.
		 */
		_insertButton: function() {
			DomUtil.prepend(this._button, this._buttonContainer);
		},
		
		/**
		 * Updates the progress of an upload.
		 *
		 * @param	{int}				uploadId	internal upload identifier
		 * @param	{XMLHttpRequestProgressEvent}	event		progress event object
		 */
		_progress: function(uploadId, event) {
			var percentComplete = Math.round(event.loaded / event.total * 100);
			
			for (var i in this._fileElements[uploadId]) {
				var progress = elByTag('PROGRESS', this._fileElements[uploadId][i]);
				if (progress.length === 1) {
					elAttr(progress[0], 'value', percentComplete);
				}
			}
		},
		
		/**
		 * Removes the button to upload files.
		 */
		_removeButton: function() {
			elRemove(this._button);
			
			DomChangeListener.trigger();
		},
		
		/**
		 * Handles a successful file upload.
		 *
		 * @param	{int}			uploadId	identifier of a file upload
		 * @param	{object<string, *>}	data		response data
		 * @param	{string}		responseText	response
		 * @param	{XMLHttpRequest}	xhr		request object
		 * @param	{object<string, *>}	requestOptions	options used to send AJAX request
		 */
		_success: function(uploadId, data, responseText, xhr, requestOptions) {
			for (var i in this._fileElements[uploadId]) {
				if (typeof data['files'][i] !== 'undefined') {
					if (this._options.imagePreview) {
						if (data['files'][i].image === null) {
							throw new Error("Excpect image for uploaded file. None given.");
						}
						
						elRemove(this._fileElements[uploadId][i]);
						
						if (elBySel('img.previewImage', this._target) !== null) {
							elBySel('img.previewImage', this._target).setAttribute('src', data['files'][i].image);
						}
						else {
							var image = elCreate('img');
							image.classList.add('previewImage');
							image.setAttribute('src', data['files'][i].image);
							image.setAttribute('style', "width: 100%;");
							elData(image, 'unique-file-id', data['files'][i].uniqueFileId);
							this._target.appendChild(image);
						}
					}
					else {
						elData(this._fileElements[uploadId][i], 'unique-file-id', data['files'][i].uniqueFileId);
						elBySel('small', this._fileElements[uploadId][i]).innerHTML = '';
						elBySel('small', this._fileElements[uploadId][i]).textContent = data['files'][i].filesize;
						elBySel('.icon', this._fileElements[uploadId][i]).classList.remove('fa-spinner');
						elBySel('.icon', this._fileElements[uploadId][i]).classList.add('fa-' + data['files'][i].icon);
					}
				}
				else if (typeof data['error'][i] !== 'undefined') {
					this._fileElements[uploadId][i].classList.add('uploadFailed');
					
					elBySel('small', this._fileElements[uploadId][i]).innerHTML = '';
					elBySel('.icon', this._fileElements[uploadId][i]).classList.remove('fa-spinner');
					elBySel('.icon', this._fileElements[uploadId][i]).classList.add('fa-ban');
					
					if (elBySel('.innerError', this._fileElements[uploadId][i]) === null) {
						var innerError = elCreate('span');
						innerError.classList = 'innerError';
						innerError.textContent = data['error'][i].errorMessage;
						DomUtil.insertAfter(innerError, elBySel('small', this._fileElements[uploadId][i]));
					}
					else {
						elBySel('.innerError', this._fileElements[uploadId][i]).textContent = data['error'][i].errorMessage;
					}
				}
				else {
					throw new Error('Unknown uploaded file for uploadId '+ uploadId +'.');
				}
			}
			
			// create delete buttons
			this._deleteHandler.rebuild();
			this.checkMaxFiles();
		},
		
		/**
		 * File input change callback to upload files.
		 *
		 * @param	{Event}		event		input change event object
		 * @param	{File}		file		uploaded file
		 * @param	{Blob}		blob		file blob
		 * @return	{(int|Array.<int>|null)}	identifier(s) for the uploaded files
		 */
		_upload: function(event, file, blob) {
			// remove failed upload elements first
			var failedUploads = DomTraverse.childrenByClass(this._target, 'uploadFailed');
			for (var i = 0, length = failedUploads.length; i < length; i++) {
				elRemove(failedUploads[i]);
			}
			
			var uploadId = null;
			
			var files = [];
			if (file) {
				files.push(file);
			}
			else if (blob) {
				var fileExtension = '';
				switch (blob.type) {
					case 'image/jpeg':
						fileExtension = '.jpg';
						break;
					
					case 'image/gif':
						fileExtension = '.gif';
						break;
					
					case 'image/png':
						fileExtension = '.png';
						break;
				}
				
				files.push({
					name: 'pasted-from-clipboard' + fileExtension
				});
			}
			else {
				files = this._fileUpload.files;
			}
			
			if (files.length && files.length + this.countFiles() <= this._options.maxFiles) {
				if (this._options.singleFileRequests) {
					uploadId = [];
					for (var i = 0, length = files.length; i < length; i++) {
						var localUploadId = this._uploadFiles([ files[i] ], blob);
						
						if (files.length !== 1) {
							this._multiFileUploadIds.push(localUploadId)
						}
						uploadId.push(localUploadId);
					}
				}
				else {
					uploadId = this._uploadFiles(files, blob);
				}
			}
			else {
				var innerError = elBySel('small.innerError:not(.innerFileError)', this._buttonContainer.parentNode);
				
				if (innerError === null) {
					innerError = elCreate('small');
					innerError.classList = 'innerError';
					DomUtil.insertAfter(innerError, this._buttonContainer);
				}
				
				innerError.textContent= WCF.Language.get('wcf.upload.error.reachedRemainingLimit').replace(/#remaining#/, this._options.maxFiles);
			}
			
			// re-create upload button to effectively reset the 'files'
			// property of the input element
			this._removeButton();
			this._createButton();
			
			return uploadId;
			
		},
		
		/**
		 * Sends the request to upload files.
		 *
		 * @param	{(FileList|Array.<File>)}	files		uploaded files
		 * @param	{Blob}				blob		file blob
		 * @return	{(int|null)}	identifier for the uploaded files
		 */
		_uploadFiles: function(files, blob) {
			var uploadId = this._createFileElements(files);
			
			// no more files left, abort
			if (!this._fileElements[uploadId].length) {
				return null;
			}
			
			var formData = new FormData();
			for (var i = 0, length = files.length; i < length; i++) {
				if (this._fileElements[uploadId][i]) {
					var internalFileId = elData(this._fileElements[uploadId][i], 'internal-file-id');
					
					if (blob) {
						formData.append('__files[' + internalFileId + ']', blob, files[i].name);
					}
					else {
						formData.append('__files[' + internalFileId + ']', files[i]);
					}
				}
			}
			
			formData.append('internalId', this._options.internalId);
			
			// recursively append additional parameters to form data
			var appendFormData = function(parameters, prefix) {
				prefix = prefix || '';
				
				for (var name in parameters) {
					if (typeof parameters[name] === 'object') {
						appendFormData(parameters[name], prefix + '[' + name + ']');
					}
					else {
						formData.append('parameters' + prefix + '[' + name + ']', parameters[name]);
					}
				}
			};
			
			appendFormData(this._getParameters());
			
			var request = new AjaxRequest({
				data: formData,
				contentType: false,
				failure: this._failure.bind(this, uploadId),
				silent: true,
				success: this._success.bind(this, uploadId),
				uploadProgress: this._progress.bind(this, uploadId),
				url: this._options.url,
				withCredentials: true
			});
			request.sendRequest();
			
			return uploadId;
		},
		
		/**
		 * Returns true if there are any pending uploads handled by this
		 * upload manager.
		 *
		 * @return	{boolean}
		 * @since	3.2
		 */
		hasPendingUploads: function() {
			for (var uploadId in this._fileElements) {
				for (var i in this._fileElements[uploadId]) {
					var progress = elByTag('PROGRESS', this._fileElements[uploadId][i]);
					if (progress.length === 1) {
						return true;
					}
				}
			}
			
			return false;
		},
		
		/**
		 * Uploads the given file blob.
		 *
		 * @param	{Blob}		blob		file blob
		 * @return	{int}		identifier for the uploaded file
		 */
		uploadBlob: function(blob) {
			return this._upload(null, null, blob);
		},
		
		/**
		 * Uploads the given file.
		 *
		 * @param	{File}		file		uploaded file
		 * @return	{int}		identifier(s) for the uploaded file
		 */
		uploadFile: function(file) {
			return this._upload(null, file);
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
			if (this.countFiles() >= this._options.maxFiles) {
				elHide(this._button);
			}
			else {
				elShow(this._button);
			}
		}
	};
	
	return Upload;
});
