$.Redactor.prototype.WoltLabReply = function() {
	"use strict";
	
	var _message;
	var _messageContent = null;
	var _messageQuickReply = null;
	var _button;
	
	return {
		init: function () {
			var messageContent = this.$editor[0].closest('.messageContent');
			var messageQuickReply = elById('messageQuickReply');
			
			if (!messageContent || !messageContent.classList.contains('messageQuickReplyContent') || !messageQuickReply || !messageQuickReply.classList.contains('messageQuickReplyCollapsed')) {
				return;
			}
			
			_messageContent = messageContent;
			_messageQuickReply = messageQuickReply;
			
			WCF.System.Event.addListener('com.woltlab.wcf.redactor2', 'showEditor', this.WoltLabReply.showEditor.bind(this));
			
			const button = document.createElement("button");
			button.classList.add("messageQuickReplyContentButton");
			button.innerHTML = '<fa-icon size="32" name="reply"></fa-icon>';
			button.append(messageContent.dataset.placeholder);
			button.addEventListener("click", () => {
				WCF.System.Event.fireEvent('com.woltlab.wcf.redactor2', 'showEditor');
			});

			messageQuickReply.append(button);
			_button = button;

			_message = messageQuickReply.querySelector(".message");
			_message.inert = true;
		},
		
		showEditor: function (skipFocus = false) {
			if (!_messageQuickReply) {
				// direct api call, but conditions are not met, be graceful
				if (!skipFocus) {
					this.WoltLabCaret.endOfEditor();
				}
				
				return;
			}
			else if (!_messageQuickReply.classList.contains('messageQuickReplyCollapsed')) {
				return;
			}
			
			_messageQuickReply.classList.remove('messageQuickReplyCollapsed');
			_button.remove();
			_message.inert = false;
			
			if (!skipFocus) {
				this.WoltLabCaret.endOfEditor();
			}
		},
	};
};
