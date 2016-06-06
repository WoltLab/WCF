$.Redactor.prototype.WoltLabAttachment = function() {
	"use strict";
	
	return {
		init: function() {
			require(['EventHandler'], (function(EventHandler) {
				EventHandler.add('com.woltlab.wcf.redactor2', 'insertAttachment_' + this.$element[0].id, this.WoltLabAttachment._insert.bind(this))
				EventHandler.add('com.woltlab.wcf.redactor2', 'deleteAttachment_' + this.$element[0].id, this.WoltLabAttachment._delete.bind(this))
			}).bind(this));
		},
		
		_insert: function(data) {
			var attachmentId = data.attachmentId;
			
			this.buffer.set();
			
			if (data.url) {
				this.insert.html('<img src="' + data.url + '" class="woltlabAttachment" data-attachment-id="' + attachmentId + '">');
			}
			else {
				// non-image attachment
				this.insert.text('[attach=' + attachmentId + '][/attach]');
			}
		},
		
		_delete: function(data) {
			var attachmentId = data.attachmentId;
			
			var editor = this.core.editor()[0];
			elBySelAll('.woltlabAttachment[data-attachment-id="' + attachmentId + '"]', editor, function(attachment) {
				elRemove(attachment);
			});
			
			// find plain text '[attach=<attachmentId>][/attach]'
			var needle = '[attach=' + attachmentId + '][/attach]';
			if (editor.textContent.indexOf(needle) !== false) {
				// code taken from http://stackoverflow.com/a/2579869
				var walker = document.createTreeWalker(
					editor,
					NodeFilter.SHOW_TEXT,
					null,
					false
				);
				
				var node, matches = [];
				while (node = walker.nextNode()) {
					if (node.textContent.indexOf(needle) !== -1) {
						matches.push(node);
					}
				}
				
				for (var i = 0, length = matches.length; i < length; i++) {
					matches[i].textContent = matches[i].textContent.replace(new RegExp('\\[attach=' + attachmentId + '\\]\\[\\/attach\\]', 'g'), '');
				}
			}
		}
	};
};
