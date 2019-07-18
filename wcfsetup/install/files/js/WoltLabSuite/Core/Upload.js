/**
 * Uploads file via AJAX.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Upload
 */
define(['AjaxRequest', 'Core', 'Dom/ChangeListener', 'Language', 'Dom/Util', 'Dom/Traverse'], function(AjaxRequest, Core, DomChangeListener, Language, DomUtil, DomTraverse) {
	"use strict";
	
	if (!COMPILER_TARGET_DEFAULT) {
		var Fake = function() {};
		Fake.prototype = {
			_createButton: function() {},
			_createFileElement: function() {},
			_createFileElements: function() {},
			_failure: function() {},
			_getParameters: function() {},
			_insertButton: function() {},
			_progress: function() {},
			_removeButton: function() {},
			_success: function() {},
			_upload: function() {},
			_uploadFiles: function() {}
		};
		return Fake;
	}
	
	/**
	 * @constructor
	 */
	function Upload(buttonContainerId, targetId, options) {
		options = options || {};
		
		if (options.className === undefined) {
			throw new Error("Missing class name.");
		}
		
		// set default options
		this._options = Core.extend({
			// name of the PHP action
			action: 'upload',
			// is true if multiple files can be uploaded at once
			multiple: false,
			// name if the upload field
			name: '__files[]',
			// is true if every file from a multi-file selection is uploaded in its own request
			singleFileRequests: false,
			// url for uploading file
			url: 'index.php?ajax-upload/&t=' + SECURITY_TOKEN
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
		if (options.multiple && this._target.nodeName !== 'UL' && this._target.nodeName !== 'OL' && this._target.nodeName !== 'TBODY') {
			throw new Error("Target element has to be list or table body if uploading multiple files is supported.");
		}
		
		this._fileElements = [];
		this._internalFileId = 0;
		
		// upload ids that belong to an upload of multiple files at once
		this._multiFileUploadIds = [];
		
		this._createButton();
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
			elAttr(this._button, 'role', 'button');

			this._fileUpload.addEventListener('focus', (function() { this._button.classList.add('active'); }).bind(this));
			this._fileUpload.addEventListener('blur', (function() { this._button.classList.remove('active'); }).bind(this));
			
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
			var progress = elCreate('progress');
			elAttr(progress, 'max', 100);
			
			if (this._target.nodeName === 'OL' || this._target.nodeName === 'UL') {
				var li = elCreate('li');
				li.innerText = file.name;
				li.appendChild(progress);
				
				this._target.appendChild(li);
				
				return li;
			}
			else if (this._target.nodeName === 'TBODY') {
				return this._createFileTableRow(file);
			}
			else {
				var p = elCreate('p');
				p.appendChild(progress);
				
				this._target.appendChild(p);
				
				return p;
			}
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
		
		_createFileTableRow: function(file) {
			throw new Error("Has to be implemented in subclass.");
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
			// does nothing
			return true;
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
		 * Return additional form data for upload requests.
		 * 
		 * @return	{object<string, *>}	additional form data
		 * @since       5.2
		 */
		_getFormData: function() {
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
			// does nothing
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
			
			if (files.length && this.validateUpload(files)) {
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
			
			// re-create upload button to effectively reset the 'files'
			// property of the input element
			this._removeButton();
			this._createButton();
			
			return uploadId;
		},
		
		/**
		 * Validates the upload before uploading them.
		 * 
		 * @param       {(FileList|Array.<File>)}	files		uploaded files
		 * @return	{boolean}
		 * @since       5.2
		 */
		validateUpload: function(files) {
			return true;
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
			
			formData.append('actionName', this._options.action);
			formData.append('className', this._options.className);
			if (this._options.action === 'upload') {
				formData.append('interfaceName', 'wcf\\data\\IUploadAction');
			}
			
			// recursively append additional parameters to form data
			var appendFormData = function(parameters, prefix) {
				prefix = prefix || '';
				
				for (var name in parameters) {
					if (typeof parameters[name] === 'object') {
						var newPrefix = prefix.length === 0 ? name : prefix + '[' + name + ']';
						appendFormData(parameters[name], newPrefix);
					}
					else {
						var dataName = prefix.length === 0 ? name : prefix + '[' + name + ']';
						formData.append(dataName, parameters[name]);
					}
				}
			};
			
			appendFormData(this._getParameters(), 'parameters');
			appendFormData(this._getFormData());
			
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
		 * @since	5.2
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
		}
	};
	
	return Upload;
});
