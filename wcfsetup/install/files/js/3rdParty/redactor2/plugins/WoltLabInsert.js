$.Redactor.prototype.WoltLabInsert = function() {
	"use strict";
	
	return {
		init: function () {
			var callback = this.opts.woltlab.placeholderCallback;
			
			var mpHtml = this.insert.html;
			this.insert.html = (function (html, data) {
				if (callback) callback = callback();
				
				this.placeholder.hide();
				this.core.editor().focus();
				
				// Firefox may have an incorrect selection if pasting into the editor using the contextual menu
				if (this.detect.isFirefox()) {
					var selection = this.selection.get();
					if (selection.anchorNode.closest('.redactor-layer') === null) {
						this.selection.restore();
						
						selection = this.selection.get();
						if (selection.anchorNode.closest('.redactor-layer') === null) {
							this.WoltLabCaret.endOfEditor();
							this.selection.save();
						}
					}
				}
				
				/** @var Element */
				var block = this.selection.block();
				
				var isEmptyEditor = (this.$editor[0].innerHTML.replace(/<\/?p>/g, '').replace(/<br>/g, '').replace(/\u200B/g, '').trim() === '');
				
				mpHtml.call(this, html, data);
				
				if (isEmptyEditor) {
					block = this.$editor[0].firstElementChild;
				}
				
				if (block && block.nodeName === 'P' && block.nextElementSibling) {
					var removeBlock = false;
					if (block.childElementCount === 0 && block.textContent.replace(/\u200B/g, '').trim() === '') {
						removeBlock = true;
					}
					else if (block.childElementCount === 1 && block.innerHTML === '<br>') {
						removeBlock = true;
					}
					
					if (removeBlock) {
						// inserting HTML tends to cause new paragraphs inserted
						// rather than using the current, empty one
						elRemove(block);
					}
				}
			}).bind(this);
			
			var mpText = this.insert.text;
			this.insert.text = (function (text) {
				if (callback) callback = callback();
				
				mpText.call(this, text);
			}).bind(this);
		}
	};
};
