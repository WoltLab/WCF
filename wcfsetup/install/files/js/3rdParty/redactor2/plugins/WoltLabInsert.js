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
				
				/** @var Element */
				var block = this.selection.block();
				
				mpHtml.call(this, html, data);
				
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
