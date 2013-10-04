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
		
		this._objectType = objectType;
		this._objectID = objectID;
		this._tmpHash = tmpHash;
		this._parentObjectID = parentObjectID;
		this._wysiwygContainerID = wysiwygContainerID;
		
		this._buttonSelector.children('p.button').click($.proxy(this._validateLimit, this));
		this._fileListSelector.find('.jsButtonInsertAttachment').click($.proxy(this._insert, this));
		
		WCF.DOMNodeRemovedHandler.addCallback('WCF.Attachment.Upload', $.proxy(this._removeLimitError, this));
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
	 * @param	object		event
	 */
	_removeLimitError: function(event) {
		var $target = $(event.target);
		if ($target.is('li.box48') && $target.parent().wcfIdentify() === this._fileListSelector.wcfIdentify()) {
			this._buttonSelector.next('small.innerError').remove();
		}
	},
	
	/**
	 * @see	WCF.Upload._upload()
	 */
	_upload: function() {
		if (this._validateLimit()) {
			this._super();
		}
		
		if (this._fileUpload) {
			// remove and re-create the upload button since the 'files' property
			// of the input field is readonly thus it can't be reset
			this._removeButton();
			this._createButton();
		}
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
		var $li = $('<li class="box48"><span class="icon icon48 icon-spinner" /><div><div><p>'+file.name+'</p><small><progress max="100"></progress></small></div><ul></ul></div></li>').data('filename', file.name);
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
				if (data.returnValues.attachments[$internalFileID]['tinyURL']) {
					$li.children('.icon-spinner').replaceWith($('<img src="' + data.returnValues.attachments[$internalFileID]['tinyURL'] + '" alt="" class="attachmentTinyThumbnail" />'));
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
				var $deleteButton = $('<li><span class="icon icon16 icon-remove pointer jsTooltip jsDeleteButton" title="'+WCF.Language.get('wcf.global.button.delete')+'" data-object-id="'+data.returnValues.attachments[$internalFileID]['attachmentID']+'" data-confirm-message="'+WCF.Language.get('wcf.attachment.delete.sure')+'" /></li>');
				$li.find('ul').append($deleteButton);
				
				if (this._wysiwygContainerID) {
					var $insertButton = $('<li><span class="icon icon16 icon-paste pointer jsTooltip jsButtonInsertAttachment" title="' + WCF.Language.get('wcf.attachment.insert') + '" data-object-id="' + data.returnValues.attachments[$internalFileID]['attachmentID'] + '" /></li>');
					$insertButton.children('.jsButtonInsertAttachment').click($.proxy(this._insert, this));
					$li.find('ul').append($insertButton);
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
		}
		
		WCF.DOMNodeInsertedHandler.execute();
	},
	
	/**
	 * Inserts an attachment into WYSIWYG editor contents.
	 * 
	 * @param	object		event
	 */
	_insert: function(event) {
		var $attachmentID = $(event.currentTarget).data('objectID');
		var $bbcode = '[attach=' + $attachmentID + '][/attach]';
		
		var $ckEditor = ($.browser.mobile) ? null : $('#' + this._wysiwygContainerID).ckeditorGet();
		if ($ckEditor !== null && $ckEditor.mode === 'wysiwyg') {
			// in design mode
			$ckEditor.insertText($bbcode);
		}
		else {
			// in source mode
			var $textarea = ($.browser.mobile) ? $('#' + this._wysiwygContainerID) : $('#' + this._wysiwygContainerID).next('.cke_editor_text').find('textarea');
			var $value = $textarea.val();
			if ($value.length == 0) {
				$textarea.val($bbcode);
			}
			else {
				var $position = $textarea.getCaret();
				$textarea.val( $value.substr(0, $position) + $bbcode + $value.substr($position) );
			}
		}
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
	}
});
