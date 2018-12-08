$.Redactor.prototype.WoltLabAttachment = function() {
	"use strict";
	
	return {
		init: function() {
			if (!this.opts.woltlab.attachments) {
				return;
			}
			
			require(['EventHandler'], (function(EventHandler) {
				EventHandler.add('com.woltlab.wcf.redactor2', 'insertAttachment_' + this.$element[0].id, this.WoltLabAttachment._insert.bind(this));
				EventHandler.add('com.woltlab.wcf.redactor2', 'deleteAttachment_' + this.$element[0].id, this.WoltLabAttachment._delete.bind(this));
				EventHandler.add('com.woltlab.wcf.redactor2', 'replaceAttachment_' + this.$element[0].id, this.WoltLabAttachment._replaceAttachment.bind(this));
			}).bind(this));
		},
		
		_insert: function(data) {
			if (this.WoltLabSource.isActive()) {
				return;
			}
			
			var attachmentId = data.attachmentId;
			
			this.buffer.set();
			
			if (data.url) {
				var id = 'wcfImgAttachment' + this.uuid;
				var img = elById(id);
				if (img) img.removeAttribute('id');
				
				this.insert.html('<img src="' + data.url + '" class="woltlabAttachment" data-attachment-id="' + attachmentId + '" id="' + id + '">');
				
				img = elById(id);
				
				var addBlankLine = true;
				var sibling = img;
				while (sibling = sibling.nextSibling) {
					if (sibling.nodeType !== Node.TEXT_NODE || sibling.textContent.replace(/\u200B/g, '').trim() !== '') {
						addBlankLine = false;
						break;
					}
				}
				
				if (addBlankLine) {
					this.caret.after(img.parentNode);
				}
				else {
					window.setTimeout((function () {
						// Safari does not properly update the caret position on insert
						var img = elById(id);
						if (img) {
							img.removeAttribute('id');
							
							// manually set the caret after the img by using a simple text-node containing just `\u200B`
							var text = img.nextSibling;
							if (!text || text.nodeType !== Node.TEXT_NODE || text.textContent !== '\u200B') {
								text = document.createTextNode('\u200B');
								img.parentNode.insertBefore(text, img.nextSibling);
							}
							
							var range = document.createRange();
							range.selectNode(text);
							range.collapse(false);
							
							var selection = window.getSelection();
							selection.removeAllRanges();
							selection.addRange(range);
						}
					}).bind(this), 10);
				}
			}
			else {
				// non-image attachment
				this.insert.text('[attach=' + attachmentId + '][/attach]');
			}
			
			this.buffer.set();
		},
		
		_replaceAttachment: function (data) {
			var img = elCreate('img');
			img.className = 'woltlabAttachment';
			img.src = data.src;
			elData(img, 'attachment-id', data.attachmentId);
			
			data.img.parentNode.insertBefore(img, data.img);
			elRemove(data.img);
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
