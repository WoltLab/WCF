/**
 * Uploads file via AJAX.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Upload
 */
define(['AjaxRequest', 'Core', 'Dom/ChangeListener', 'Language', 'Dom/Util', 'Dom/Traverse'], function(AjaxRequest, Core, DomChangeListener, Language, DomUtil, DomTraverse) {
	"use strict";
	
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
			url: 'index.php/AJAXUpload/?t=' + SECURITY_TOKEN
		}, options);
		
		this._options.url = WCF.convertLegacyURL(this._options.url);
		
		this._buttonContainer = elById(buttonContainerId);
		if (this._buttonContainer === null) {
			throw new Error("Element id '" + buttonContainerId + "' is unknown.");
		}
		
		this._target = elById(targetId);
		if (targetId === null) {
			throw new Error("Element id '" + targetId + "' is unknown.");
		}
		if (options.multiple && this._target.nodeName !== 'UL' && this._target.nodeName !== 'OL') {
			throw new Error("Target element has to be list when allowing upload of multiple files.");
		}
		
		this._fileElements = [];
		this._internalFileId = 0;
		
		this._createButton();
	};
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
			this._button.classList.add('button');
			this._button.classList.add('uploadButton');
			
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
			
			if (files.length) {
				if (this._options.singleFileRequests) {
					uploadId = [];
					for (var i = 0, length = files.length; i < length; i++) {
						uploadId.push(this._uploadFiles([ files[i] ], blob));
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
			formData.append('interfaceName', 'wcf\\data\\IUploadAction');
			
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
				url: this._options.url
			});
			request.sendRequest();
			
			return uploadId;
		}
	};
	
	return Upload;
});
