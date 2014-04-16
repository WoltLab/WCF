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
		$('<ul class="formAttachmentList clearfix" />').hide().appendTo(this._attachmentsContainer);
		$('<dl class="wide"><dt></dt><dd><div data-max-size="{@$attachmentHandler->getMaxSize()}"></div><small>' + WCF.String.unescapeHTML(WCF.Language.get('wcf.attachment.upload.limits')) + '</small></dd></dl>').appendTo(this._attachmentsContainer);
		
		var $options = this.getOption('wattachment');
		new WCF.Attachment.Upload(this._attachmentsContainer.find('> dl > dd > div'), this._attachmentsContainer.children('ul'), $options.objectType, $options.objectID, $options.tmpHash, $options.parentObjectID, $options.maxCount, this.$source.wcfIdentify());
		new WCF.Action.Delete('wcf\\data\\attachment\\AttachmentAction', '.formAttachmentList > li');
	}
};
