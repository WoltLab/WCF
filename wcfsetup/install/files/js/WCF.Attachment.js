"use strict";

/**
 * Namespace for attachments
 */
WCF.Attachment = {};

if (COMPILER_TARGET_DEFAULT) {
	/**
	 * Attachment upload function
	 *
	 * @see        WCF.Upload
	 */
	WCF.Attachment.Upload = WCF.Upload.extend({
		/**
		 * list of upload ids which should be automatically inserted
		 * @var        array<integer>
		 */
		_autoInsert: [],
		
		/**
		 * reference to 'Insert All' button
		 * @var        jQuery
		 */
		_insertAllButton: null,
		
		/**
		 * object type of the object the uploaded attachments belong to
		 * @var        string
		 */
		_objectType: '',
		
		/**
		 * id of the object the uploaded attachments belong to
		 * @var        string
		 */
		_objectID: 0,
		
		/**
		 * temporary hash to identify uploaded attachments
		 * @var        string
		 */
		_tmpHash: '',
		
		/**
		 * id of the parent object of the object the uploaded attachments belongs to
		 * @var        string
		 */
		_parentObjectID: 0,
		
		/**
		 * editor id
		 * @var        string
		 */
		_editorId: '',
		
		/**
		 * replace img element on load
		 * @var Object
		 */
		_replaceOnLoad: {},
		
		/**
		 * additional options
		 * @var Object
		 */
		_options: {},
		
		/**
		 * @see        WCF.Upload.init()
		 */
		init: function (buttonSelector, fileListSelector, objectType, objectID, tmpHash, parentObjectID, maxUploads, editorId, options) {
			this._super(buttonSelector, fileListSelector, 'wcf\\data\\attachment\\AttachmentAction', {
				multiple: true,
				maxUploads: maxUploads
			});
			
			this._autoInsert = [];
			this._objectType = objectType;
			this._objectID = parseInt(objectID);
			this._tmpHash = tmpHash;
			this._parentObjectID = parseInt(parentObjectID);
			this._editorId = editorId;
			this._options = $.extend(true, this._options, options || {});
			
			this._buttonSelector.children('p.button').click($.proxy(this._validateLimit, this));
			this._fileListSelector.find('.jsButtonInsertAttachment').click($.proxy(this._insert, this));
			this._fileListSelector.find('.jsButtonAttachmentInsertThumbnail').click($.proxy(this._insert, this));
			this._fileListSelector.find('.jsButtonAttachmentInsertFull').click($.proxy(this._insert, this));
			
			WCF.DOMNodeRemovedHandler.addCallback('WCF.Attachment.Upload', $.proxy(this._removeLimitError, this));
			WCF.System.Event.addListener('com.woltlab.wcf.action.delete', 'attachment_' + this._editorId, $.proxy(this._removeLimitError, this));
			
			this._makeSortable();
			
			this._insertAllButton = $('<p class="button jsButtonAttachmentInsertAll">' + WCF.Language.get('wcf.attachment.insertAll') + '</p>').hide().appendTo(this._buttonSelector);
			this._insertAllButton.click($.proxy(this._insertAll, this));
			
			if (this._fileListSelector.children('li:not(.uploadFailed)').length) {
				this._insertAllButton.show();
			}
			
			if (this._editorId) {
				WCF.System.Event.addListener('com.woltlab.wcf.redactor2', 'submit_' + this._editorId, this._submitInline.bind(this));
				WCF.System.Event.addListener('com.woltlab.wcf.redactor2', 'reset_' + this._editorId, this._reset.bind(this));
				WCF.System.Event.addListener('com.woltlab.wcf.redactor2', 'dragAndDrop_' + this._editorId, this._editorUpload.bind(this));
				WCF.System.Event.addListener('com.woltlab.wcf.redactor2', 'pasteFromClipboard_' + this._editorId, this._editorUpload.bind(this));
				
				WCF.System.Event.addListener('com.woltlab.wcf.redactor2', 'autosaveMetaData_' + this._editorId, (function (data) {
					if (!data.tmpHashes || !Array.isArray(data.tmpHashes)) {
						data.tmpHashes = [];
					}
					
					var index = data.tmpHashes.indexOf(tmpHash);
					
					var count = this._fileListSelector.children('li:not(.uploadFailed)').length;
					if (count > 0) {
						if (index === -1) {
							data.tmpHashes.push(tmpHash);
						}
					}
					else if (index !== -1) {
						data.tmpHashes.splice(index);
					}
				}).bind(this));
				
				var metacodeAttachUuid = WCF.System.Event.addListener('com.woltlab.wcf.redactor2', 'metacode_attach_' + this._editorId, (function (data) {
					var images = this._getImageAttachments();
					var attachmentId = data.attributes[0] || 0;
					if (images.hasOwnProperty(attachmentId)) {
						var thumbnail = data.attributes[2];
						thumbnail = (thumbnail === true || thumbnail === 'true' || ~~thumbnail > 0);
						
						var image = elCreate('img');
						image.className = 'woltlabAttachment';
						image.src = images[attachmentId][(thumbnail ? 'thumbnailUrl' : 'url')];
						elData(image, 'attachment-id', attachmentId);
						
						var float = data.attributes[1] || 'none';
						if (float === 'left') image.classList.add('messageFloatObjectLeft');
						else if (float === 'right') image.classList.add('messageFloatObjectRight');
						
						var metacode = data.metacode;
						metacode.parentNode.insertBefore(image, metacode);
						elRemove(metacode);
						
						data.cancel = true;
					}
				}).bind(this));
				
				WCF.System.Event.addListener('com.woltlab.wcf.redactor2', 'destroy_' + this._editorId, (function () {
					WCF.System.Event.removeAllListeners('com.woltlab.wcf.redactor2', 'submit_' + this._editorId);
					WCF.System.Event.removeAllListeners('com.woltlab.wcf.redactor2', 'reset_' + this._editorId);
					WCF.System.Event.removeAllListeners('com.woltlab.wcf.redactor2', 'insertAttachment_' + this._editorId);
					WCF.System.Event.removeAllListeners('com.woltlab.wcf.redactor2', 'dragAndDrop_' + this._editorId);
					WCF.System.Event.removeAllListeners('com.woltlab.wcf.redactor2', 'pasteFromClipboard_' + this._editorId);
					WCF.System.Event.removeAllListeners('com.woltlab.wcf.redactor2', 'autosaveMetaData_' + this._editorId);
					
					WCF.System.Event.removeListener('com.woltlab.wcf.redactor2', 'metacode_attach_' + this._editorId, metacodeAttachUuid);
				}).bind(this));
			}
		},
		
		/**
		 * Handles drag & drop uploads and clipboard paste.
		 *
		 * @param        object                data
		 */
		_editorUpload: function (data) {
			var $uploadID, replace = null;
			
			// show tab
			this._fileListSelector.closest('.messageTabMenu').messageTabMenu('showTab', 'attachments', true);
			
			if (data.file) {
				$uploadID = this._upload(undefined, data.file);
			}
			else {
				$uploadID = this._upload(undefined, undefined, data.blob);
				replace = data.replace || null;
			}
			
			if (replace === null) {
				this._autoInsert.push($uploadID);
			}
			else {
				this._replaceOnLoad[$uploadID] = replace;
			}
			
			data.uploadID = $uploadID;
		},
		
		/**
		 * Sets the attachments representing an image.
		 *
		 * @return      {Object}
		 */
		_getImageAttachments: function () {
			var images = {};
			
			this._fileListSelector.children('li').each(function (index, attachment) {
				var $attachment = $(attachment);
				if ($attachment.data('isImage')) {
					images[~~$attachment.data('objectID')] = {
						thumbnailUrl: $attachment.find('.jsButtonAttachmentInsertThumbnail').data('url'),
						url: $attachment.find('.jsButtonAttachmentInsertFull').data('url')
					};
				}
			});
			
			return images;
		},
		
		/**
		 * Adds parameters for the inline editor.
		 *
		 * @param        object                data
		 */
		_submitInline: function (data) {
			if (this._tmpHash) {
				data.tmpHash = this._tmpHash;
				
				var metaData = {};
				WCF.System.Event.fireEvent('com.woltlab.wcf.redactor2', 'getMetaData_' + this._editorId, metaData);
				if (metaData.tmpHashes && Array.isArray(metaData.tmpHashes) && metaData.tmpHashes.length > 0) {
					data.tmpHash += ',' + metaData.tmpHashes.join(',');
				}
			}
		},
		
		/**
		 * Resets the attachment container.
		 */
		_reset: function () {
			this._fileListSelector.hide().empty();
			this._insertAllButton.hide();
			this._validateLimit();
		},
		
		/**
		 * Validates upload limits.
		 *
		 * @return        boolean
		 */
		_validateLimit: function () {
			var $innerError = this._buttonSelector.next('small.innerError');
			
			// check maximum uploads
			var $max = this._options.maxUploads - this._fileListSelector.children('li:not(.uploadFailed)').length;
			var $filesLength = (this._fileUpload) ? this._fileUpload.prop('files').length : 0;
			if ($max <= 0 || $max < $filesLength) {
				// reached limit
				var $errorMessage = ($max <= 0) ? WCF.Language.get('wcf.attachment.upload.error.reachedLimit') : WCF.Language.get('wcf.attachment.upload.error.reachedRemainingLimit').replace(/#remaining#/, $max);
				if (!$innerError.length) {
					$innerError = $('<small class="innerError" />').insertAfter(this._buttonSelector);
				}
				
				$innerError.html($errorMessage);
				
				return false;
			}
			
			// remove previous errors
			$innerError.remove();
			
			return true;
		},
		
		/**
		 * Removes the limit error message.
		 *
		 * @param        object                data
		 */
		_removeLimitError: function (data) {
			var $listItems = this._fileListSelector.children('li');
			if (!$listItems.filter(':not(.uploadFailed)').length) {
				this._insertAllButton.hide();
			}
			
			if (!$listItems.length) {
				this._fileListSelector.hide();
			}
			
			if (this._editorId && data.button) {
				WCF.System.Event.fireEvent('com.woltlab.wcf.redactor2', 'deleteAttachment_' + this._editorId, {
					attachmentId: data.button.data('objectID')
				});
			}
		},
		
		/**
		 * @see        WCF.Upload._upload()
		 */
		_upload: function (event, file, blob) {
			var _super = this._super.bind(this);
			
			require([
				'WoltLabSuite/Core/FileUtil',
				'WoltLabSuite/Core/ImageResizer',
				'WoltLabSuite/Core/Ajax/Status'
			]).then((function (modules) {
				var FileUtil = modules[0];
				var ImageResizer = modules[1];
				var AjaxStatus = modules[2];
				
				AjaxStatus.show();
				
				var $files = [];
				
				if (file) {
					$files.push(file);
				}
				else if (blob) {
					$files.push(FileUtil.blobToFile(blob, 'pasted-from-clipboard'));
				}
				else {
					$files = this._fileUpload.prop('files');
				}
				
				// Default action is to resolve with the unaltered list of files
				var $promise = Promise.resolve($files);
				
				if (this._options.autoScale && this._options.autoScale.enable) {
					var $maxSize = this._buttonSelector.data('maxSize');
					
					// Create an ImageResizer instance
					var $resizer = new ImageResizer();
					var $maxWidth = this._options.autoScale.maxWidth;
					var $maxHeight = this._options.autoScale.maxHeight;
					var $quality = this._options.autoScale.quality;
					
					if (this._options.autoScale.fileType !== 'keep') {
						$resizer.setFileType(this._options.autoScale.fileType);
					}
					
					// Resize the images in series.
					// As our resizer is based on Pica it will use multiple workers per image if possible.
					$promise = Array.prototype.reduce.call($files, (function (acc, file) {
						return acc.then((function (arr) {
							var $timeout = new Promise(function (resolve, reject) {
								setTimeout(function () {
									resolve(file);
								}, 10e3); // Timeout per image
							});
							
							var $promise = $resizer.resize(file, $maxWidth, $maxHeight, $quality, file.size > $maxSize, $timeout)
							.then((function (result) {
								if (result.image instanceof File) {
									return result.image;
								}
								
								var $fileType = undefined;
								
								if (this._options.autoScale.fileType === 'keep') {
									$fileType = file.type;
								}
								
								return $resizer.getFile(result, file.name, $fileType, $quality);
							}).bind(this))
							.then(function (resizedFile) {
								if (resizedFile.size > file.size) {
									console.debug('[WCF.Attachment] File size of "' + file.name + '" increased, uploading untouched image.');
									return file;
								}
								
								return resizedFile;
							})
							.catch(function (error) {
								console.debug('[WCF.Attachment] Failed to resize image "' + file.name + '":', error);
								return file;
							});
							
							return Promise.race([$timeout, $promise])
								.then(function (file) {
									arr.push(file);
									return arr;
								});
						}).bind(this));
					}).bind(this), Promise.resolve([]));
				}
				
				return $promise.then((function (files) {
					var $uploadID = undefined;
					
					if (this._validateLimit()) {
						$uploadID = _super(event, undefined, undefined, files);
					}
					
					if (this._fileUpload) {
						// remove and re-create the upload button since the 'files' property
						// of the input field is readonly thus it can't be reset
						this._removeButton();
						this._createButton();
					}
					
					return $uploadID;
				}).bind(this)).finally(AjaxStatus.hide);
			}).bind(this)).catch(function (error) {
				console.debug('[WCF.Attachment] Failed to upload attachments:', error);
			});
		},
		
		/**
		 * @see        WCF.Upload._createUploadMatrix()
		 */
		_createUploadMatrix: function (files) {
			// remove failed uploads
			this._fileListSelector.children('li.uploadFailed').remove();
			
			return this._super(files);
		},
		
		/**
		 * @see        WCF.Upload._getParameters()
		 */
		_getParameters: function () {
			return {
				objectType: this._objectType,
				objectID: this._objectID,
				tmpHash: this._tmpHash,
				parentObjectID: this._parentObjectID
			};
		},
		
		/**
		 * @see        WCF.Upload._initFile()
		 */
		_initFile: function (file) {
			var $li = $('<li class="box64"><span class="icon icon64 fa-spinner" /><div><div><p>' + file.name + '</p><small><progress max="100"></progress></small></div><ul></ul></div></li>').data('filename', file.name);
			this._fileListSelector.append($li);
			this._fileListSelector.show();
			
			// validate file size
			if (this._buttonSelector.data('maxSize') < file.size) {
				// remove progress bar
				$li.find('progress').remove();
				
				// upload icon
				$li.children('.fa-spinner').removeClass('fa-spinner').addClass('fa-ban');
				
				// error message
				$li.find('div > div').append($('<small class="innerError">' + WCF.Language.get('wcf.attachment.upload.error.tooLarge') + '</small>'));
				$li.addClass('uploadFailed');
			}
			
			return $li;
		},
		
		/**
		 * Returns true if thumbnails are enabled and should be
		 * used instead of the original images.
		 *
		 * @return      {boolean}
		 * @protected
		 */
		_useThumbnail: function() {
			return elDataBool(this._fileListSelector[0], 'enable-thumbnails');
		},
		
		/**
		 * @see        WCF.Upload._success()
		 */
		_success: function (uploadID, data) {
			var attachmentData;
			for (var $i in this._uploadMatrix[uploadID]) {
				if (!this._uploadMatrix[uploadID].hasOwnProperty($i)) {
					continue;
				}
				
				// get li
				var $li = this._uploadMatrix[uploadID][$i];
				
				// remove progress bar
				$li.find('progress').remove();
				
				// get filename and check result
				var $filename = $li.data('filename');
				var $internalFileID = $li.data('internalFileID');
				if (data.returnValues && data.returnValues.attachments[$internalFileID]) {
					attachmentData = data.returnValues.attachments[$internalFileID];
					
					// show thumbnail
					if (attachmentData.tinyURL) {
						$li.children('.fa-spinner').replaceWith($('<img src="' + attachmentData.tinyURL + '" alt="" class="attachmentTinyThumbnail" />'));
						
						$li.data('height', attachmentData.height);
						$li.data('width', attachmentData.width);
						elData($li[0], 'is-image', attachmentData.isImage);
					}
					// show file icon
					else {
						$li.children('.fa-spinner').removeClass('fa-spinner').addClass('fa-' + attachmentData.iconName);
					}
					
					// update attachment link
					var $link = $('<a href=""></a>');
					$link.text($filename).attr('href', attachmentData.url);
					$link[0].target = '_blank';
					
					if (attachmentData.isImage != 0) {
						$link.addClass('jsImageViewer').attr('title', $filename);
					}
					$li.find('p').empty().append($link);
					
					// update file size
					$li.find('small').append(attachmentData.formattedFilesize);
					
					// init buttons
					var $buttonList = $li.find('ul').addClass('buttonGroup');
					var $deleteButton = $('<li><span class="button small jsDeleteButton" data-object-id="' + attachmentData.attachmentID + '" data-confirm-message="' + WCF.Language.get('wcf.attachment.delete.sure') + '" data-event-name="attachment_' + this._editorId + '">' + WCF.Language.get('wcf.global.button.delete') + '</span></li>');
					$buttonList.append($deleteButton);
					
					$li.data('objectID', attachmentData.attachmentID);
					
					if (this._editorId) {
						if (attachmentData.tinyURL || (!this._useThumbnail() && attachmentData.isImage)) {
							if (attachmentData.thumbnailURL) {
								var $insertThumbnail = $('<li><span class="button small jsButtonAttachmentInsertThumbnail" data-object-id="' + attachmentData.attachmentID + '" data-url="' + WCF.String.escapeHTML(attachmentData.thumbnailURL) + '">' + WCF.Language.get('wcf.attachment.insertThumbnail') + '</span></li>').appendTo($buttonList);
								$insertThumbnail.children('span.button').click($.proxy(this._insert, this));
							}
							
							var $insertOriginal = $('<li><span class="button small jsButtonAttachmentInsertFull" data-object-id="' + attachmentData.attachmentID + '" data-url="' + WCF.String.escapeHTML(attachmentData.url) + '">' + WCF.Language.get('wcf.attachment.insertFull') + '</span></li>').appendTo($buttonList);
							$insertOriginal.children('span.button').click($.proxy(this._insert, this));
						}
						else {
							var $insertPlain = $('<li><span class="button small jsButtonAttachmentInsertPlain" data-object-id="' + attachmentData.attachmentID + '">' + WCF.Language.get('wcf.attachment.insert') + '</span></li>');
							$insertPlain.appendTo($buttonList).children('span.button').click($.proxy(this._insert, this));
						}
					}
					
					if (this._replaceOnLoad.hasOwnProperty(uploadID)) {
						if (!$li.hasClass('uploadFailed')) {
							var img = this._replaceOnLoad[uploadID];
							if (img && img.parentNode) {
								WCF.System.Event.fireEvent('com.woltlab.wcf.redactor2', 'replaceAttachment_' + this._editorId, {
									attachmentId: attachmentData.attachmentID,
									img: img,
									src: (attachmentData.thumbnailURL) ? attachmentData.thumbnailURL : attachmentData.url
								});
							}
						}
						
						this._replaceOnLoad[uploadID] = null;
					}
				}
				else {
					// upload icon
					$li.children('.fa-spinner').removeClass('fa-spinner').addClass('fa-ban');
					var $errorMessage = '';
					
					// error handling
					if (data.returnValues && data.returnValues.errors[$internalFileID]) {
						var errorData = data.returnValues.errors[$internalFileID];
						$errorMessage = errorData.errorType;
						
						if ($errorMessage === 'uploadFailed' && errorData.additionalData.phpLimitExceeded) {
							$errorMessage = 'uploadPhpLimit';
						}
					}
					else {
						// unknown error
						$errorMessage = 'uploadFailed';
					}
					
					$li.find('div > div').append($('<small class="innerError">' + WCF.Language.get('wcf.attachment.upload.error.' + $errorMessage) + '</small>'));
					$li.addClass('uploadFailed');
				}
				
				if (WCF.inArray(uploadID, this._autoInsert)) {
					this._autoInsert.splice(this._autoInsert.indexOf(uploadID), 1);
					
					if (!$li.hasClass('uploadFailed')) {
						var btn = $li.find('.jsButtonAttachmentInsertThumbnail');
						if (!btn.length) btn = $li.find('.jsButtonAttachmentInsertFull');
						
						btn.trigger('click');
					}
				}
			}
			
			this._makeSortable();
			
			if (this._fileListSelector.children('li:not(.uploadFailed)').length) {
				this._insertAllButton.show();
			}
			else {
				this._insertAllButton.hide();
			}
			
			WCF.DOMNodeInsertedHandler.execute();
		},
		
		/**
		 * Inserts an attachment into WYSIWYG editor contents.
		 *
		 * @param        {Event}                event
		 */
		_insert: function (event) {
			WCF.System.Event.fireEvent('com.woltlab.wcf.redactor2', 'insertAttachment_' + this._editorId, {
				attachmentId: elData(event.currentTarget, 'object-id'),
				url: elData(event.currentTarget, 'url')
			});
		},
		
		/**
		 * Inserts all attachments at once.
		 */
		_insertAll: function () {
			var selector = (this._useThumbnail()) ? '.jsButtonAttachmentInsertThumbnail, .jsButtonAttachmentInsertPlain' : '.jsButtonAttachmentInsertFull, .jsButtonAttachmentInsertPlain';
			this._fileListSelector.children('li:not(.uploadFailed)').find(selector).trigger('click');
		},
		
		/**
		 * @see        WCF.Upload._error()
		 */
		_error: function (data) {
			// mark uploads as failed
			this._fileListSelector.find('li').each(function (index, listItem) {
				var $listItem = $(listItem);
				if ($listItem.children('.fa-spinner').length) {
					// upload icon
					$listItem.addClass('uploadFailed').children('.fa-spinner').removeClass('fa-spinner').addClass('fa-ban');
					$listItem.find('div > div').append($('<small class="innerError">' + (data.responseJSON && data.responseJSON.message ? data.responseJSON.message : WCF.Language.get('wcf.attachment.upload.error.uploadFailed')) + '</small>'));
				}
			});
		},
		
		/**
		 * Initializes sorting for uploaded attachments.
		 */
		_makeSortable: function () {
			var $attachments = this._fileListSelector.children('li:not(.uploadFailed)');
			if (!$attachments.length) {
				return;
			}
			
			$attachments.addClass('sortableAttachment').children('img').addClass('sortableNode');
			
			if (!this._fileListSelector.hasClass('sortableList')) {
				this._fileListSelector.addClass('sortableList');
				
				require(['Environment'], (function (Environment) {
					if (Environment.platform() === 'desktop') {
						new WCF.Sortable.List(this._fileListSelector.parent().wcfIdentify(), '', 0, {
							axis: false,
							items: 'li.sortableAttachment',
							toleranceElement: null,
							start: function (event, ui) {
								ui.placeholder[0].style.setProperty('height', ui.helper[0].offsetHeight + 'px', '');
							},
							update: (function () {
								var $attachmentIDs = [];
								this._fileListSelector.children('li:not(.uploadFailed)').each(function (index, listItem) {
									$attachmentIDs.push($(listItem).data('objectID'));
								});
								
								if ($attachmentIDs.length) {
									new WCF.Action.Proxy({
										autoSend: true,
										data: {
											actionName: 'updatePosition',
											className: 'wcf\\data\\attachment\\AttachmentAction',
											parameters: {
												attachmentIDs: $attachmentIDs,
												objectID: this._objectID,
												objectType: this._objectType,
												tmpHash: this._tmpHash
											}
										}
									});
								}
							}).bind(this)
						}, true);
					}
				}).bind(this));
			}
		}
	});
}
else {
	WCF.Attachment.Upload = WCF.Upload.extend({
		_autoInsert: {},
		_insertAllButton: {},
		_objectType: "",
		_objectID: 0,
		_tmpHash: "",
		_parentObjectID: 0,
		_editorId: "",
		_replaceOnLoad: {},
		init: function() {},
		_editorUpload: function() {},
		_getImageAttachments: function() {},
		_submitInline: function() {},
		_reset: function() {},
		_validateLimit: function() {},
		_removeLimitError: function() {},
		_upload: function() {},
		_createUploadMatrix: function() {},
		_getParameters: function() {},
		_initFile: function() {},
		_success: function() {},
		_insert: function() {},
		_insertAll: function() {},
		_error: function() {},
		_makeSortable: function() {},
		_name: "",
		_buttonSelector: {},
		_fileListSelector: {},
		_fileUpload: {},
		_className: "",
		_iframe: {},
		_internalFileID: 0,
		_options: {},
		_uploadMatrix: {},
		_supportsAJAXUpload: true,
		_overlay: {},
		_createButton: function() {},
		_insertButton: function() {},
		_removeButton: function() {},
		_progress: function() {},
		_showOverlay: function() {},
		_evaluateResponse: function() {},
		_getFilename: function() {}
	});
}
