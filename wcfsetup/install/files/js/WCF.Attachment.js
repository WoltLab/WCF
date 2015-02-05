"use strict";

/**
 * Namespace for attachments
 */
WCF.Attachment = {};

/**
 * Attachment upload function
 * 
 * @see	WCF.Upload
 */
WCF.Attachment.Upload = WCF.Upload.extend({
	/**
	 * list of upload ids which should be automatically inserted
	 * @var	array<integer>
	 */
	_autoInsert: [ ],
	
	/**
	 * reference to 'Insert All' button
	 * @var	jQuery
	 */
	_insertAllButton: null,
	
	/**
	 * object type of the object the uploaded attachments belong to
	 * @var	string
	 */
	_objectType: '',
	
	/**
	 * id of the object the uploaded attachments belong to
	 * @var	string
	 */
	_objectID: 0,
	
	/**
	 * temporary hash to identify uploaded attachments
	 * @var	string
	 */
	_tmpHash: '',
	
	/**
	 * id of the parent object of the object the uploaded attachments belongs to
	 * @var	string
	 */
	_parentObjectID: 0,
	
	/**
	 * container if of WYSIWYG editor
	 * @var	string
	 */
	_wysiwygContainerID: '',
	
	/**
	 * @see	WCF.Upload.init()
	 */
	init: function(buttonSelector, fileListSelector, objectType, objectID, tmpHash, parentObjectID, maxUploads, wysiwygContainerID) {
		this._super(buttonSelector, fileListSelector, 'wcf\\data\\attachment\\AttachmentAction', { multiple: true, maxUploads: maxUploads });
		
		this._autoInsert = [ ];
		this._objectType = objectType;
		this._objectID = parseInt(objectID);
		this._tmpHash = tmpHash;
		this._parentObjectID = parseInt(parentObjectID);
		this._wysiwygContainerID = wysiwygContainerID;
		
		this._buttonSelector.children('p.button').click($.proxy(this._validateLimit, this));
		this._fileListSelector.find('.jsButtonInsertAttachment').click($.proxy(this._insert, this));
		this._fileListSelector.find('.jsButtonAttachmentInsertThumbnail').click($.proxy(this._insert, this));
		this._fileListSelector.find('.jsButtonAttachmentInsertFull').click($.proxy(this._insert, this));
		
		//WCF.DOMNodeRemovedHandler.addCallback('WCF.Attachment.Upload', $.proxy(this._removeLimitError, this));
		WCF.System.Event.addListener('com.woltlab.wcf.action.delete', 'attachment_' + this._wysiwygContainerID, $.proxy(this._removeLimitError, this));
		
		this._makeSortable();
		
		this._insertAllButton = $('<p class="button jsButtonAttachmentInsertAll">' + WCF.Language.get('wcf.attachment.insertAll') + '</p>').hide().appendTo(this._buttonSelector);
		this._insertAllButton.click($.proxy(this._insertAll, this));
		
		if (this._fileListSelector.children('li:not(.uploadFailed)').length) {
			this._insertAllButton.show();
		}
		
		if (this._wysiwygContainerID) {
			WCF.System.Event.addListener('com.woltlab.wcf.messageOptionsInline', 'submit_' + this._wysiwygContainerID, $.proxy(this._submitInline, this));
			WCF.System.Event.addListener('com.woltlab.wcf.messageOptionsInline', 'prepareExtended_' + this._wysiwygContainerID, $.proxy(this._prepareExtended, this));
			WCF.System.Event.addListener('com.woltlab.wcf.redactor', 'reset', $.proxy(this._reset, this));
			WCF.System.Event.addListener('com.woltlab.wcf.redactor', 'upload_' + this._wysiwygContainerID, $.proxy(this._editorUpload, this));
			WCF.System.Event.addListener('com.woltlab.wcf.redactor', 'getImageAttachments_' + this._wysiwygContainerID, $.proxy(this._getImageAttachments, this));
		}
	},
	
	/**
	 * Handles drag & drop uploads and clipboard paste.
	 * 
	 * @param	object		data
	 */
	_editorUpload: function(data) {
		var $uploadID;
		
		// show tab
		this._fileListSelector.closest('.messageTabMenu').messageTabMenu('showTab', 'attachments', true);
		
		if (data.file) {
			$uploadID = this._upload(undefined, data.file);
		}
		else {
			$uploadID = this._upload(undefined, undefined, data.blob);
		}
		
		this._autoInsert.push($uploadID);
		data.uploadID = $uploadID;
	},
	
	/**
	 * Sets the attachments representing an image.
	 * 
	 * @param	object		data
	 */
	_getImageAttachments: function(data) {
		this._fileListSelector.children('li').each(function(index, attachment) {
			var $attachment = $(attachment);
			if ($attachment.children('img.attachmentTinyThumbnail').length) {
				data.imageAttachments[parseInt($attachment.data('objectID'))] = {
					height: parseInt($attachment.data('height')),
					width: parseInt($attachment.data('width'))
				};
			}
		});
	},
	
	/**
	 * Adds parameters for the inline editor.
	 * 
	 * @param	object		data
	 */
	_submitInline: function(data) {
		if (this._tmpHash) {
			data.tmpHash = this._tmpHash;
		}
	},
	
	/**
	 * Adds parameters send to the server before jumping to extended form.
	 * 
	 * @param	object		data
	 */
	_prepareExtended: function(data) {
		if (!this._objectID && this._tmpHash && this._fileListSelector.children('li:not(.uploadFailed)').length) {
			data.tmpHash = this._tmpHash;
		}
	},
	
	/**
	 * Resets the attachment container.
	 */
	_reset: function() {
		this._fileListSelector.hide().empty();
		this._insertAllButton.hide();
		this._validateLimit();
	},
	
	/**
	 * Validates upload limits.
	 * 
	 * @return	boolean
	 */
	_validateLimit: function() {
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
	 * @param	object		data
	 */
	_removeLimitError: function(data) {
		var $listItems = this._fileListSelector.children('li');
		if (!$listItems.filter(':not(.uploadFailed)').length) {
			this._insertAllButton.hide();
		}
		
		if (!$listItems.length) {
			setTimeout((function() {
				this._fileListSelector.wcfBlindOut();
			}).bind(this), 250);
		}
		
		if (this._wysiwygContainerID) {
			$('#' + this._wysiwygContainerID).redactor('wbbcode.removeAttachment', data.button.data('objectID'));
		}
	},
	
	/**
	 * @see	WCF.Upload._upload()
	 */
	_upload: function(event, file, blob) {
		var $uploadID = undefined;
		
		if (this._validateLimit()) {
			$uploadID = this._super(event, file, blob);
		}
		
		if (this._fileUpload) {
			// remove and re-create the upload button since the 'files' property
			// of the input field is readonly thus it can't be reset
			this._removeButton();
			this._createButton();
		}
		
		return $uploadID;
	},
	
	/**
	 * @see	WCF.Upload._createUploadMatrix()
	 */
	_createUploadMatrix: function(files) {
		// remove failed uploads
		this._fileListSelector.children('li.uploadFailed').remove();
		
		return this._super(files);
	},
	
	/**
	 * @see	WCF.Upload._getParameters()
	 */
	_getParameters: function() {
		return {
			objectType: this._objectType,
			objectID: this._objectID,
			tmpHash: this._tmpHash,
			parentObjectID: this._parentObjectID
		};
	},
	
	/**
	 * @see	WCF.Upload._initFile()
	 */
	_initFile: function(file) {
		var $li = $('<li class="box64"><span class="icon icon48 icon-spinner" /><div><div><p>'+file.name+'</p><small><progress max="100"></progress></small></div><ul></ul></div></li>').data('filename', file.name);
		this._fileListSelector.append($li);
		this._fileListSelector.show();
		
		// validate file size
		if (this._buttonSelector.data('maxSize') < file.size) {
			// remove progress bar
			$li.find('progress').remove();
			
			// upload icon
			$li.children('.icon-spinner').removeClass('icon-spinner').addClass('icon-ban-circle');
			
			// error message
			$li.find('div > div').append($('<small class="innerError">' + WCF.Language.get('wcf.attachment.upload.error.tooLarge') + '</small>'));
			$li.addClass('uploadFailed');
		}
		
		return $li;
	},
	
	/**
	 * @see	WCF.Upload._success()
	 */
	_success: function(uploadID, data) {
		for (var $i in this._uploadMatrix[uploadID]) {
			// get li
			var $li = this._uploadMatrix[uploadID][$i];
			
			// remove progress bar
			$li.find('progress').remove();
			
			// get filename and check result
			var $filename = $li.data('filename');
			var $internalFileID = $li.data('internalFileID');
			if (data.returnValues && data.returnValues.attachments[$internalFileID]) {
				// show thumbnail
				if (data.returnValues.attachments[$internalFileID].tinyURL) {
					$li.children('.icon-spinner').replaceWith($('<img src="' + data.returnValues.attachments[$internalFileID]['tinyURL'] + '" alt="" class="attachmentTinyThumbnail" />'));
					
					$li.data('height', data.returnValues.attachments[$internalFileID].height);
					$li.data('width', data.returnValues.attachments[$internalFileID].width);
				}
				// show file icon
				else {
					$li.children('.icon-spinner').removeClass('icon-spinner').addClass('icon-paper-clip');
				}
				
				// update attachment link
				var $link = $('<a href=""></a>');
				$link.text($filename).attr('href', data.returnValues.attachments[$internalFileID]['url']);
				
				if (data.returnValues.attachments[$internalFileID]['isImage'] != 0) {
					$link.addClass('jsImageViewer').attr('title', $filename);
				}
				$li.find('p').empty().append($link);
				
				// update file size
				$li.find('small').append(data.returnValues.attachments[$internalFileID]['formattedFilesize']);
				
				// init buttons
				var $buttonList = $li.find('ul').addClass('buttonGroup');
				var $deleteButton = $('<li><span class="button small jsDeleteButton" data-object-id="'+data.returnValues.attachments[$internalFileID]['attachmentID']+'" data-confirm-message="'+WCF.Language.get('wcf.attachment.delete.sure')+'" data-event-name="attachment_' + this._wysiwygContainerID + '">' + WCF.Language.get('wcf.global.button.delete') + '</span></li>');
				$buttonList.append($deleteButton);
				
				$li.data('objectID', data.returnValues.attachments[$internalFileID].attachmentID);
				
				if (this._wysiwygContainerID) {
					if (data.returnValues.attachments[$internalFileID].tinyURL) {
						var $insertThumbnail = $('<li><span class="button small jsButtonAttachmentInsertThumbnail" data-object-id="' + data.returnValues.attachments[$internalFileID].attachmentID + '">' + WCF.Language.get('wcf.attachment.insertThumbnail') + '</span></li>').appendTo($buttonList);
						var $insertOriginal = $('<li><span class="button small jsButtonAttachmentInsertFull" data-object-id="' + data.returnValues.attachments[$internalFileID].attachmentID + '">' + WCF.Language.get('wcf.attachment.insertFull') + '</span></li>').appendTo($buttonList);
						
						$insertThumbnail.children('span.button').click($.proxy(this._insert, this));
						$insertOriginal.children('span.button').click($.proxy(this._insert, this));
					}
					else {
						var $insertPlain = $('<li><span class="button small jsButtonAttachmentInsertPlain" data-object-id="' + data.returnValues.attachments[$internalFileID].attachmentID + '">' + WCF.Language.get('wcf.attachment.insert') + '</span></li>');
						$insertPlain.appendTo($buttonList).children('span.button').click($.proxy(this._insert, this));
					}
				}
			}
			else {
				// upload icon
				$li.children('.icon-spinner').removeClass('icon-spinner').addClass('icon-ban-circle');
				var $errorMessage = '';
				
				// error handling
				if (data.returnValues && data.returnValues.errors[$internalFileID]) {
					$errorMessage = data.returnValues.errors[$internalFileID]['errorType'];
				}
				else {
					// unknown error
					$errorMessage = 'uploadFailed';
				}
				
				$li.find('div > div').append($('<small class="innerError">'+WCF.Language.get('wcf.attachment.upload.error.'+$errorMessage)+'</small>'));
				$li.addClass('uploadFailed');
			}
			
			// fix webkit rendering bug
			$li.css('display', 'block');
			
			if (WCF.inArray(uploadID, this._autoInsert)) {
				this._autoInsert.splice(this._autoInsert.indexOf(uploadID), 1);
				
				if (!$li.hasClass('uploadFailed')) {
					WCF.System.Event.fireEvent('com.woltlab.wcf.attachment', 'autoInsert_' + this._wysiwygContainerID, {
						attachment: '[attach=' + data.returnValues.attachments[$internalFileID].attachmentID + '][/attach]',
						uploadID: uploadID
					});
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
	 * @param	object		event
	 * @param	integer		attachmentID
	 */
	_insert: function(event, attachmentID) {
		var $attachmentID = (event === null) ? attachmentID : $(event.currentTarget).data('objectID');
		var $insertFull = (event !== null) ? $(event.currentTarget).hasClass('jsButtonAttachmentInsertFull') : false;
		
		if ($.browser.redactor) {
			$('#' + this._wysiwygContainerID).redactor('wbbcode.insertAttachment', $attachmentID, $insertFull);
		}
	},
	
	/**
	 * Inserts all attachments at once.
	 */
	_insertAll: function() {
		this._fileListSelector.children('li:not(.uploadFailed)').each($.proxy(function(index, attachment) {
			this._insert(null, $(attachment).data('objectID'));
		}, this));
	},
	
	/**
	 * @see	WCF.Upload._error()
	 */
	_error: function(data) {
		// mark uploads as failed
		this._fileListSelector.find('li').each(function(index, listItem) {
			var $listItem = $(listItem);
			if ($listItem.children('.icon-spinner').length) {
				// upload icon
				$listItem.addClass('uploadFailed').children('.icon-spinner').removeClass('icon-spinner').addClass('icon-ban-circle');
				$listItem.find('div > div').append($('<small class="innerError">' + (data.responseJSON && data.responseJSON.message ? data.responseJSON.message : WCF.Language.get('wcf.attachment.upload.error.uploadFailed')) + '</small>'));
			}
		});
	},
	
	/**
	 * Initializes sorting for uploaded attachments.
	 */
	_makeSortable: function() {
		var $attachments = this._fileListSelector.children('li:not(.uploadFailed)');
		if (!$attachments.length) {
			return;
		}
		
		$attachments.addClass('sortableAttachment').children('img').addClass('sortableNode');
		
		if (!this._fileListSelector.hasClass('sortableList')) {
			this._fileListSelector.addClass('sortableList');
			
			var self = this;
			new WCF.Sortable.List(this._fileListSelector.parent().wcfIdentify(), '', 0, {
				axis: false,
				items: 'li.sortableAttachment',
				toleranceElement: null,
				update: function(event, ui) {
					var $attachmentIDs = [ ];
					self._fileListSelector.children('li:not(.uploadFailed)').each(function(index, listItem) {
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
									objectID: self._objectID,
									objectType: self._objectType,
									tmpHash: self._tmpHash
								}
							}
						});
					}
				}
			}, true);
		}
	}
});
