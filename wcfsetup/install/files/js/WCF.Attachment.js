"use strict";

/**
 * Namespace for attachments
 */
WCF.Attachment = {};

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
		
		WCF.System.Event.addListener('com.woltlab.wcf.action.delete', 'attachment', this._onDelete.bind(this));
		
		this._makeSortable();
		
		// for backwards compatibility, the object is still created but only inserted
		// if an editor is used
		this._insertAllButton = $('<p class="button jsButtonAttachmentInsertAll">' + WCF.Language.get('wcf.attachment.insertAll') + '</p>').hide();
		
		if (this._editorId) {
			this._insertAllButton.appendTo(this._buttonSelector);
			this._insertAllButton.click($.proxy(this._insertAll, this));
			
			if (this._fileListSelector.children('li:not(.uploadFailed)').length) {
				this._insertAllButton.show();
			}
			
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
					var thumbnailWidth = ~~$('#' + this._editorId).data('redactor').opts.woltlab.attachmentThumbnailWidth;
					
					var thumbnail = data.attributes[2];
					thumbnail = (thumbnail === true || thumbnail === 'true' || (~~thumbnail && ~~thumbnail <= thumbnailWidth));
					
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
			
			var syncUuid = WCF.System.Event.addListener('com.woltlab.wcf.redactor2', 'sync_' + this._tmpHash, this._sync.bind(this));
			
			WCF.System.Event.addListener('com.woltlab.wcf.redactor2', 'destroy_' + this._editorId, (function () {
				WCF.System.Event.removeAllListeners('com.woltlab.wcf.redactor2', 'submit_' + this._editorId);
				WCF.System.Event.removeAllListeners('com.woltlab.wcf.redactor2', 'reset_' + this._editorId);
				WCF.System.Event.removeAllListeners('com.woltlab.wcf.redactor2', 'insertAttachment_' + this._editorId);
				WCF.System.Event.removeAllListeners('com.woltlab.wcf.redactor2', 'dragAndDrop_' + this._editorId);
				WCF.System.Event.removeAllListeners('com.woltlab.wcf.redactor2', 'pasteFromClipboard_' + this._editorId);
				WCF.System.Event.removeAllListeners('com.woltlab.wcf.redactor2', 'autosaveMetaData_' + this._editorId);
				
				WCF.System.Event.removeListener('com.woltlab.wcf.redactor2', 'metacode_attach_' + this._editorId, metacodeAttachUuid);
				WCF.System.Event.removeListener('com.woltlab.wcf.redactor2', 'sync_' + this._tmpHash, syncUuid);
			}).bind(this));
		}
	},
	
	/**
	 * Handles drag & drop uploads and clipboard paste.
	 *
	 * @param        object                data
	 */
	_editorUpload: function (data) {
		var replace = null;
		
		// show tab
		this._fileListSelector.closest('.messageTabMenu').messageTabMenu('showTab', 'attachments', true);
		
		var callbackUploadId = (function(uploadId) {
			if (replace === null) {
				this._autoInsert.push(uploadId);
			}
			else {
				this._replaceOnLoad[uploadId] = replace;
			}

			data.uploadID = uploadId;
		}).bind(this);
		
		if (data.file) {
			this._upload(undefined, data.file, undefined, callbackUploadId);
		}
		else {
			this._upload(undefined, undefined, data.blob, callbackUploadId);
			replace = data.replace || null;
		}
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
	_upload: function (event, file, blob, callbackUploadId) {
		var _super = this._super.bind(this);
		
		require([
			'WoltLabSuite/Core/FileUtil',
			'WoltLabSuite/Core/Image/ImageUtil',
			'WoltLabSuite/Core/Image/Resizer',
			'WoltLabSuite/Core/Ajax/Status'
		], (function (FileUtil, ImageUtil, ImageResizer, AjaxStatus) {
			AjaxStatus.show();
			
			var files = [];
			
			if (file) {
				files.push(file);
			}
			else if (blob) {
				files.push(FileUtil.blobToFile(blob, 'pasted-from-clipboard'));
			}
			else {
				files = this._fileUpload.prop('files');
			}
			
			// We resolve with the unaltered list of files in case auto scaling is disabled.
			var promise = Promise.resolve(files);
			
			if (this._options.autoScale && this._options.autoScale.enable) {
				var maxSize = this._buttonSelector.data('maxSize');
				
				var resizer = new ImageResizer();
				
				// Resize the images in series.
				// As our resizer is based on Pica it will use multiple workers per image if possible.
				promise = Array.prototype.reduce.call(files, (function (acc, file) {
					return acc.then((function (arr) {
						// Ignore anything that is not one of the 3 basic image types.
						if (['image/png', 'image/gif', 'image/jpeg'].indexOf(file.type) === -1) {
							arr.push(file);
							return arr;
						}
						
						var timeout = new Promise(function (resolve, reject) {
							// We issue one timeout per image, thus multiple timeout
							// handlers will run in parallel
							setTimeout(function () {
								resolve(file);
							}, 10000);
						});
						
						var promise = resizer.loadFile(file)
							.then((function (result) {
								var exif = result.exif;
								var maxWidth = this._options.autoScale.maxWidth;
								var maxHeight = this._options.autoScale.maxHeight;
								var quality = this._options.autoScale.quality;
								
								if (window.devicePixelRatio >= 2) {
									var realWidth = window.screen.width * window.devicePixelRatio;
									var realHeight = window.screen.height * window.devicePixelRatio;
									// Check whether the width of the image is roughly the width of the physical screen, and
									// the height of the image is at least the height of the physical screen.
									if (realWidth - 10 < result.image.width && result.image.width < realWidth + 10 && realHeight - 10 < result.image.height) {
										// This appears to be a screenshot from a HiDPI device in portrait mode: Scale to logical size
										maxWidth = Math.min(maxWidth, window.screen.width);
									}
								}
								
								return resizer.resize(result.image, maxWidth, maxHeight, quality, file.size > maxSize, timeout)
									.then((function (resizedImage) {
										// Check whether the image actually was resized
										if (resizedImage === undefined) {
											return file;
										}
										
										var fileType = this._options.autoScale.fileType;
										
										if (this._options.autoScale.fileType === 'keep' || ImageUtil.containsTransparentPixels(resizedImage)) {
											fileType = file.type;
										}
										
										return resizer.saveFile({
											exif: exif,
											image: resizedImage
										}, file.name, fileType, quality);
									}).bind(this))
									.then(function (resizedFile) {
										if (resizedFile.size > file.size) {
											console.debug('[WCF.Attachment] File size of "' + file.name + '" increased, uploading untouched image.');
											return file;
										}
										
										return resizedFile;
									});
							}).bind(this))
							.catch(function (error) {
								console.debug('[WCF.Attachment] Failed to resize image "' + file.name + '":', error);
								return file;
							});
						
						return Promise.race([timeout, promise])
							.then(function (file) {
								arr.push(file);
								return arr;
							});
					}).bind(this));
				}).bind(this), Promise.resolve([]));
			}
			
			promise.then((function (files) {
				var uploadID = undefined;
				
				if (this._validateLimit()) {
					uploadID = _super(event, undefined, undefined, files);
				}
				
				if (this._fileUpload) {
					// remove and re-create the upload button since the 'files' property
					// of the input field is readonly thus it can't be reset
					this._removeButton();
					this._createButton();
				}
				
				if (typeof callbackUploadId === 'function') {
					callbackUploadId(uploadID);
				}
				
				return uploadID;
			}).bind(this))
			.catch(function (error) {
				console.debug('[WCF.Attachment] Failed to upload attachments:', error);
			})
			.finally(AjaxStatus.hide);
		}).bind(this), function (error) {
			console.debug('[WCF.Attachment] Failed to load modules:', error);
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
		var $li = $('<li class="box64 formAttachmentListItem"><span class="icon icon64 fa-spinner" /><div><div><p>' + file.name + '</p><small><progress max="100"></progress></small></div><ul></ul></div></li>').data('filename', file.name);
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
	 * @deprecated 5.3
	 */
	_useThumbnail: function() {
		return true;
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
				
				elData($li[0], 'object-id', attachmentData.attachmentID);
				
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
				var $deleteButton = $('<li><span class="button small jsDeleteButton" data-object-id="' + attachmentData.attachmentID + '" data-confirm-message="' + WCF.Language.get('wcf.attachment.delete.sure') + '" data-event-name="attachment">' + WCF.Language.get('wcf.global.button.delete') + '</span></li>');
				$buttonList.append($deleteButton);
				
				$li.data('objectID', attachmentData.attachmentID);
				
				if (this._editorId) {
					if (attachmentData.tinyURL) {
						if (attachmentData.thumbnailURL) {
							$('<li><span class="button small jsButtonAttachmentInsertThumbnail" data-object-id="' + attachmentData.attachmentID + '" data-url="' + WCF.String.escapeHTML(attachmentData.thumbnailURL) + '">' + WCF.Language.get('wcf.attachment.insertThumbnail') + '</span></li>').appendTo($buttonList);
						}
						
						$('<li><span class="button small jsButtonAttachmentInsertFull" data-object-id="' + attachmentData.attachmentID + '" data-url="' + WCF.String.escapeHTML(attachmentData.url) + '">' + WCF.Language.get('wcf.attachment.insertFull') + '</span></li>').appendTo($buttonList);
					}
					else {
						$('<li><span class="button small jsButtonAttachmentInsertPlain" data-object-id="' + attachmentData.attachmentID + '">' + WCF.Language.get('wcf.attachment.insert') + '</span></li>').appendTo($buttonList);
					}
				}
				
				this._triggerSync('new', {
					html: $li[0].outerHTML
				});
				
				this._registerEditorButtons($li[0]);
				
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
		
		this._rebuildInterface();
	},
	
	_rebuildInterface: function () {
		this._makeSortable();
		
		if (this._fileListSelector.children('li:not(.uploadFailed)').length) {
			this._insertAllButton.show();
		}
		else {
			this._insertAllButton.hide();
		}
		
		WCF.DOMNodeInsertedHandler.execute();
	},
	
	_registerEditorButtons: function (attachment) {
		if (this._editorId) {
			elBySelAll('.jsButtonAttachmentInsertThumbnail, .jsButtonAttachmentInsertFull, .jsButtonAttachmentInsertPlain', attachment, (function(button) {
				button.addEventListener('click', this._insert.bind(this));
			}).bind(this));
		}
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
		var attachment, button;
		for (var i = 0, length = this._fileListSelector[0].childNodes.length; i < length; i++) {
			attachment = this._fileListSelector[0].childNodes[i];
			if (attachment.nodeName === 'LI' && !attachment.classList.contains('uploadFailed')) {
				button = elBySel('.jsButtonAttachmentInsertThumbnail, .jsButtonAttachmentInsertPlain', attachment);
				
				if (button === null) {
					button = elBySel('.jsButtonAttachmentInsertFull, .jsButtonAttachmentInsertPlain', attachment);
				}

				window.jQuery(button).trigger('click');
			}
		}
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
	},
	
	/**
	 * @param {Object} data
	 * @param {jQuery} data.button
	 * @param {jQuery} data.container
	 */
	_onDelete: function (data) {
		var objectId = elData(data.button[0], 'object-id');
		var attachment = elBySel('.formAttachmentListItem[data-object-id="' + objectId + '"]', this._fileListSelector[0]);
		if (attachment !== null) {
			elRemove(attachment);
		}
		
		this._removeLimitError(data);
	},
	
	/**
	 * @param {Object} payload
	 * @param {Object} payload.data
	 * @param {Object} payload.source
	 * @param {string} payload.type
	 */
	_sync: function (payload) {
		if (payload.source === this) {
			return;
		}
		
		switch (payload.type) {
			case 'new':
				this._syncNew(payload.data);
				break;
				
			default:
				throw new Error("Unexpected type '" + payload.type + "'");
		}
	},
	
	/**
	 * @param {Object} data
	 */
	_syncNew: function (data) {
		require(['Dom/Util'], (function (DomUtil) {
			var fragment = DomUtil.createFragmentFromHtml(data.html);
			var attachment = elBySel('li', fragment);
			attachment.id = '';
			
			this._registerEditorButtons(attachment);
			
			this._fileListSelector[0].appendChild(attachment);
			
			elShow(this._fileListSelector[0]);
			
			this._rebuildInterface();
		}).bind(this));
	},
	
	/**
	 * @param {string} type
	 * @param {Object} data
	 */
	_triggerSync: function (type, data) {
		WCF.System.Event.fireEvent('com.woltlab.wcf.redactor2', 'sync_' + this._tmpHash, {
			source: this,
			type: type,
			data: data
		});
	}
});
