/**
 * TODO: complete implementation
 */
define(['Core', 'Dom/ChangeListener', 'Dom/Traverse', 'Language', 'List', 'Upload'], function(Core, DomChangeListener, DomTraverse, Language, List, Upload) {
	"use strict";
	
	function AttachmentUpload(buttonContainerId, targetId, tmpHash, objectType, objectId, parentObjectId, maxUploads, maxSize, wysiwygContainerId) {
		this._tmpHash = tmpHash;
		this._objectType = objectType;
		this._objectId = ~~objectId;
		this._parentObjectId = ~~parentObjectID;
		this._wysiwygContainerId = wysiwygContainerId;
		
		this._autoInsert = new List();
		
		Upload.call(this, 'uploadImage', 'styleImage', {
			className: 'wcf\\data\\attachment\\AttachmentAction',
			maxSize: ~~maxSize,
			maxUploads: ~~maxUploads,
			multiple: true
		});
		
		// add event listeners
		DomTraverse.childByClass(this._button, '.button').addEventListener(WCF_CLICK_EVENT, this._validateLimit.bind(this));
		elByClass(this._target, 'jsButtonInsertAttachment').addEventListener(WCF_CLICK_EVENT, this._insert.bind(this));
		elByClass(this._target, 'jsButtonAttachmentInsertThumbnail').addEventListener(WCF_CLICK_EVENT, this._insert.bind(this));
		elByClass(this._target, 'jsButtonAttachmentInsertFull').addEventListener(WCF_CLICK_EVENT, this._insert.bind(this));
		
		// TODO: WCF.System.Event.addListener('com.woltlab.wcf.action.delete', 'attachment_' + this._wysiwygContainerId, $.proxy(this._removeLimitError, this));
		
		// TODO: this._makeSortable();
		
		this._insertAllButton = elCreate('p');
		this._insertAllButton.className = 'button jsButtonAttachmentInsertAll';
		this._insertAllButton.textContent = Language.get('wcf.attachment.insertAll');
		if (DomTraverse.childBySel(this._target, 'li:not(.uploadFailed)')) {
			elHide(this._insertAllButton);
		}
		this._insertAllButton.addEventListener(WCF_CLICK_EVENT, this._insertAll.bind(this));
		this._button.appendChild(this._insertAllButton);
		
		if (this._wysiwygContainerId) {
			// TODO: WCF.System.Event.addListener('com.woltlab.wcf.messageOptionsInline', 'submit_' + this._wysiwygContainerId, $.proxy(this._submitInline, this));
			// TODO: WCF.System.Event.addListener('com.woltlab.wcf.messageOptionsInline', 'prepareExtended_' + this._wysiwygContainerId, $.proxy(this._prepareExtended, this));
			// TODO: WCF.System.Event.addListener('com.woltlab.wcf.redactor', 'reset', $.proxy(this._reset, this));
			// TODO: WCF.System.Event.addListener('com.woltlab.wcf.redactor', 'upload_' + this._wysiwygContainerId, $.proxy(this._editorUpload, this));
			// TODO: WCF.System.Event.addListener('com.woltlab.wcf.redactor', 'getImageAttachments_' + this._wysiwygContainerId, $.proxy(this._getImageAttachments, this));
		}
	};
	Core.inherit(AttachmentUpload, Upload,{
		/**
		 * @see	WoltLab/WCF/Upload#_createFileElement
		 */
		_createFileElement: function(file) {
			var listItem = elCreate('li');
			listItem.className = 'box64';
			elData(listItem, 'filename', filename);
			this._target.appendChild(listItem);
			elShow(this._target);
			
			var span = elCreate('span');
			if (this._options.maxSize >= file.size) {
				span.className = 'icon icon48 fa-spinnner';
			}
			else {
				span.className = 'icon icon48 fa-ban-circle';
			}
			listItem.appendChild(span);
			
			var div = elCreate('div');
			listItem.appendChild(div);
			
			var div2 = elCreate('div');
			div.appendChild(div2);
			
			var p = elCreate('p');
			p.textContent = file.name;
			div2.appendChild(p);
			
			var small = elCreate('small');
			div2.appendChild(small);
			
			if (this._options.maxSize >= file.size) {
				var progress = elCreate('progress');
				elAttr(progress, 'max', 100);
			}
			
			div.appendChild(elCreate('ul'));
			
			if (this._options.maxSize < file.size) {
				small = elCreate('small');
				small.className = 'innerError';
				small.textContent = Language.get('wcf.attachment.upload.error.tooLarge');
				div2.appendChild(small);
				
				listItem.classList.add('uploadFailed');
			}
		},
		
		/**
		 * @see	WoltLab/WCF/Upload#_createFileElements
		 */
		_createFileElements: function(files) {
			var failedUploads = DomTraverse.childrenBySel(this._target, 'li.uploadFailed');
			for (var i = 0, length = failedUploads.length; i < length; i++) {
				this._target.removeChild(failedUploads[i]);
			}
			
			return Upload.prototype._createFileElements.call(this, files);
		},
		
		/**
		 * @see	WoltLab/WCF/Upload#_getParameters
		 */
		_getParameters: function() {
			return {
				objectID: this._objectId,
				objectType: this._objectType,
				parentObjectID: this._parentObjectId,
				tmpHash: this._tmpHash
			};
		},
		
		/**
		 * @see	WoltLab/WCF/Upload#_success
		 */
		_success: function(uploadId, data) {
			for (var i = 0, length = this._fileElements[uploadId].length; i < length; i++) {
				var listItem = this._fileElements[uploadId][i];
				
				var progress = elByTag(listItem, 'PROGRESS');
				elRemove(progress);
				
				var filename = elData(listItem, 'filename');
				var internalFileId = elData(listItem, 'internal-file-id');
				
				var icon = DomTraverse.childByClass(listItem, 'fa-spinner');
				
				if (data.returnValues && data.returnValues.attachments[internalFileId]) {
					var attachment = data.returnValues.attachments[internalFileId];
					if (attachment.tinyURL) {
						var img = elCreate('img');
						img.className = 'attachmentTinyThumbnail';
						elAttr(img, 'src', attachment.tinyURL);
						elAttr(img, 'alt', '');
						icon.parentNode.replaceChild(icon, img);
						
						elData(listItem, 'height', attachment.height);
						elData(listItem, 'width', attachment.width);
					}
					else {
						// TODO: Use FileUtil.getIconClassByMimeType()?
						icon.classList.remove('fa-spinner');
						icon.classList.add('fa-paper-clip');
					}
					
					var p = elByTag(listItem, 'P');
					p.innerHtml = '';
					
					var a = elCreate('a');
					a.textContent = filename;
					elAttr(a, 'href', attachment.url);
					
					if (attachment.isImage) {
						a.className = 'jsImageViewer';
						elAttr(a, 'title', filename);
					}
					
					p.appendChild(a);
					
					elByTag(listItem, 'SMALL').textContent = attachment.formattedFilesize;
					
					var ul = elByTag(listItem, 'UL');
					ul.classList.add('buttonGroup');
					
					var deleteButton = elCreate('li');
					ul.appendChild(deleteButton);
					
					var span = elCreate('span');
					span.className = 'button small jsDeleteButton';
					span.textContent = Language.get('wcf.global.button.delete');
					elData(span, 'object-id', attachment.attachmentID);
					elData(span, 'confirm-message', Language.get('wcf.attachment.delete.sure'));
					if (this._wysiwygContainerId) {
						elData(span, 'event-name', 'attachment_' + this._wysiwygContainerId);
					}
					deleteButton.appendChild(span);
					
					elData(span, 'object-id', attachment.attachmentID);
					
					if (this._wysiwygContainerId) {
						if (attachment.tinyURL) {
							var insertThumbnailButton = elCreate('li');
							ul.appendChild(insertThumbnailButton);
							
							span = elCreate('span');
							span.className = 'button small jsButtonAttachmentInsertThumbnail';
							span.textContent = Language.get('wcf.global.button.insertThumbnail');
							elData(span, 'object-id', attachment.attachmentID);
							span.addEventListener(WCF_CLICK_EVENT, this._insert.bind(this));
							insertThumbnailButton.appendChild(span);
							
							var insertOriginalButton = elCreate('li');
							ul.appendChild(insertOriginalButton);
							
							span = elCreate('span');
							span.className = 'button small jsButtonAttachmentInsertFull';
							span.textContent = Language.get('wcf.global.button.insertFull');
							elData(span, 'object-id', attachment.attachmentID);
							span.addEventListener(WCF_CLICK_EVENT, this._insert.bind(this));
							insertOriginalButton.appendChild(span);
						}
						else {
							var insertPlainButton = elCreate('li');
							ul.appendChild(insertPlainButton);
							
							span = elCreate('span');
							span.className = 'button small jsButtonAttachmentInsertPlain';
							span.textContent = Language.get('wcf.global.button.insert');
							elData(span, 'object-id', attachment.attachmentID);
							span.addEventListener(WCF_CLICK_EVENT, this._insert.bind(this));
							insertPlainButton.appendChild(span);
						}
					}
				}
				else {
					icon.classList.removeClass('fa-spinner');
					icon.classList.addClass('fa-ban-circle');
					
					var errorType = 'uploadFailed';
					if (data.returnValues && data.returnValues.errors[internalFileId]) {
						errorType = data.returnValues.errors[internalFileId].errorType;
					}
					
					var small = elCreate('small');
					small.className = 'innerError';
					small.textContent = Language.get('wcf.attachment.upload.error.' + errorType);
					elBySel(listItem, 'div > div').appendChild(small);
					
					listItem.classList.add('uploadFailed');
				}
				
				// fix WebKit rendinering bug
				// TODO: still necessary?
				listItem.style.setProperty('display', 'block');
				
				if (this._autoInsert.has(uploadId)) {
					this._autoInsert['delete'](uploadId);
					
					if (listItem.classList.contains('uploadFailed')) {
						// TODO: WCF.System.Event.fireEvent('com.woltlab.wcf.attachment', 'autoInsert_' + this._wysiwygContainerId, {
						//	attachment: '[attach=' + attachment.attachmentID + '][/attach]',
						//	uploadID: uploadId
						//});
					}
				}
			}
			
			// TODO: this._makeSortable();
			
			if (DomTraverse.childrenBySel(this._target, 'li:not(.uploadFailed)').length) {
				elShow(this._insertAllButton);
			}
			else {
				elHide(this._insertAllButton);
			}
			
			DomChangeListener.trigger();
		},
		
		/**
		 * @see	WoltLab/WCF/Upload#_upload
		 */
		_upload: function(event, file, blob) {
			if (this._validateLimit()) {
				Upload.prototype._upload.call(this, event, file, blob);
			}
		},
		
		_validateLimit: function() {
			var innerError = DomTraverse.nextBySel(this._button, 'small.innerError');
			
			var remainingUploads = this._options.maxUploads - DomTraverse.childrenBySel(this._target, 'li:not(.uploadFailed)').length;
			if (remainingUploads <= 0 || remainingUploads < this._fileUpload.files.length) {
				if (!innerError) {
					innerError = elCreate('small');
					innerError.className = 'innerError';
					DomUtil.insertAfter(innerError, this._button);
				}
				
				if (remainingUploads <= 0) {
					innerError.textContent = Language.get('wcf.attachment.upload.error.reachedLimit');
				}
				else {
					innerError.textContent = Language.get('wcf.attachment.upload.error.reachedRemainingLimit', {
						remaining: remainingUploads
					});
				}
				
				return false;
			}
			
			if (innerError) {
				elRemove(innerError);
			}
			
			return true;
		}
	});
});
