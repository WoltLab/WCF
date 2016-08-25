$.Redactor.prototype.WoltLabInsert = function() {
	"use strict";
	
	return {
		init: function () {
			var mpHtml = this.insert.html;
			this.insert.html = (function (html, data) {
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
		}
	};
};
