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
				
				if (block && block.nodeName === 'P' && block.nextElementSibling && !block.childElementCount && block.textContent.replace(/\u200B/g, '').trim() === '') {
					// inserting HTML tends to cause new paragraphs inserted
					// rather than using the current, empty one
					elRemove(block);
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
