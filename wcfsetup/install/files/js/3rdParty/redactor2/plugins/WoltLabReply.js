$.Redactor.prototype.WoltLabReply = function() {
	"use strict";
	
	var _callbackClick = null;
	var _messageContent = null;
	var _messageQuickReply = null;
	
	return {
		init: function () {
			var messageContent = this.$editor[0].closest('.messageContent');
			var messageQuickReply = elById('messageQuickReply');
			
			if (!messageContent || !messageContent.classList.contains('messageQuickReplyContent') || !messageQuickReply || !messageQuickReply.classList.contains('messageQuickReplyCollapsed')) {
				return;
			}
			
			_callbackClick = this.WoltLabReply._click.bind(this);
			_messageContent = messageContent;
			_messageQuickReply = messageQuickReply;
			
			WCF.System.Event.addListener('com.woltlab.wcf.redactor2', 'showEditor', this.WoltLabReply.showEditor.bind(this));
			
			_messageContent.addEventListener('click', _callbackClick);
		},
		
		showEditor: function () {
			if (!_messageQuickReply) {
				// direct api call, but conditions are not met, be graceful
				this.WoltLabCaret.endOfEditor();
				return;
			}
			else if (!_messageQuickReply.classList.contains('messageQuickReplyCollapsed')) {
				return;
			}
			
			_messageQuickReply.classList.remove('messageQuickReplyCollapsed');
			_messageContent.removeEventListener('click', _callbackClick);
			
			this.WoltLabCaret.endOfEditor();
		},
		
		_click: function (event) {
			event.preventDefault();
			
			WCF.System.Event.fireEvent('com.woltlab.wcf.redactor2', 'showEditor');
		}
	};
};
