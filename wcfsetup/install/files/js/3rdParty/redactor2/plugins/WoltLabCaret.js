$.Redactor.prototype.WoltLabCaret = function() {
	"use strict";
	
	return {
		init: function () {
			var mpAfter = this.caret.after;
			this.caret.after = (function (node) {
				node = this.caret.prepare(node);
				
				if (this.utils.isBlockTag(node.tagName)) {
					this.WoltLabCaret._addParagraphAfterBlock(node);
				}
				
				mpAfter.call(this, node);
			}).bind(this);
			
			this.$editor[0].addEventListener('mouseup', this.WoltLabCaret._handleEditorClick.bind(this));
		},
		
		_handleEditorClick: function (event) {
			if (event.target !== this.$editor[0]) {
				return;
			}
			
			if (!this.selection.get().isCollapsed) {
				return;
			}
			
			var block = this.selection.block();
			if (block === false) {
				return;
			}
			
			if (block.nodeName === 'P') {
				return;
			}
			
			this.buffer.set();
			
			// click occurred onto the empty editor space, but before or after a block element
			var insertBefore = (event.clientY < block.getBoundingClientRect().top);
			var p = elCreate('p');
			p.textContent = '\u200B';
			block.parentNode.insertBefore(p, (insertBefore ? block : block.nextSibling));
			
			this.caret.end(p);
		},
		
		_addParagraphAfterBlock: function (block) {
			var nextElement = block.nextElementSibling;
			if (nextElement && (nextElement.nodeName === 'P' || this.utils.isBlockTag(nextElement.nodeName))) {
				// valid target
				return;
			}
			
			nextElement = elCreate('p');
			nextElement.textContent = '\u200B';
			block.parentNode.insertBefore(nextElement, block.nextSibling);
		}
	};
};
