if (!RedactorPlugins) var RedactorPlugins = {};

/**
 * Provides file uploads for Redactor.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
RedactorPlugins.wupload = {
	/**
	 * attachments container object
	 * @var	jQuery
	 */
	_attachmentsContainer: null,
	
	/**
	 * Initializes the RedactorPlugins.wupload plugin.
	 */
	init: function() {
		var self = this;
		this.buttonReplace('upload', 'upload', 'Upload', function() { self._attachmentsContainer.toggle(); });
		this.buttonAwesome('upload', 'fa-upload');
		
		this._initAttachments();
	},
	
	/**
	 * Initializes the attachments user interface.
	 */
	_initAttachments: function() {
		this._attachmentsContainer = $('<div class="redactorAttachmentContainer" />').hide().appendTo(this.$box);
		var $attachmentList = $('<ul class="formAttachmentList clearfix" />').hide().appendTo(this._attachmentsContainer);
		$('<dl class="wide"><dt></dt><dd><div data-max-size="{@$attachmentHandler->getMaxSize()}"></div><small>' + WCF.String.unescapeHTML(WCF.Language.get('wcf.attachment.upload.limits')) + '</small></dd></dl>').appendTo(this._attachmentsContainer);
		
		var $options = this.getOption('wattachment');
		if ($options.attachments.length) {
			for (var $i = 0; $i < $options.attachments.length; $i++) {
				var $attachment = $options.attachments[$i];
				var $listItem = $('<li class="box48" />').data('objectID', $attachment.attachmentID);
				if ($attachment.tinyThumbnailUrl) {
					$('<img src="' + $attachment.tinyThumbnailUrl + '" alt="" class="attachmentTinyThumbnail" />').appendTo($listItem);
				}
				else {
					$('<span class="icon icon48 icon-paper-clip" />').appendTo($listItem);
				}
				
				var $div = $('<div />').appendTo($listItem);
				$('<div><p><a href="' + $attachment.url + '"' + ($attachment.isImage ? ' title="' + $attachment.filename + '" class="jsImageViewer"' : '') + '>' + $attachment.filename + '</a></p></div>').appendTo($div);
				var $list = $('<ul />').appendTo($div);
				$('<li><span class="icon icon16 icon-remove pointer jsTooltip jsDeleteButton " title="' + WCF.Language.get('wcf.global.button.delete') + '" data-object-id="' + $attachment.attachmentID + '" data-confirm-message="' + WCF.Language.get('wcf.attachment.delete.sure') + '"></span></li>').appendTo($list);
				$('<li><span class="icon icon16 icon-paste pointer jsTooltip jsButtonInsertAttachment" title="' + WCF.Language.get('wcf.attachment.insert') + '" data-object-id="' + $attachment.attachmentID + '"></span></li>').appendTo($list);
				
				$listItem.appendTo($attachmentList);
				
				this._attachmentsContainer.show();
				$attachmentList.show();
			}
		}
		
		new WCF.Attachment.Upload(this._attachmentsContainer.find('> dl > dd > div'), this._attachmentsContainer.children('ul'), $options.objectType, $options.objectID, $options.tmpHash, $options.parentObjectID, $options.maxCount, this.$source.wcfIdentify());
		new WCF.Action.Delete('wcf\\data\\attachment\\AttachmentAction', '.formAttachmentList > li');
	}
};
