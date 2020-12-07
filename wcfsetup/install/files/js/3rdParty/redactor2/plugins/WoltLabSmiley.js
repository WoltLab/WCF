$.Redactor.prototype.WoltLabSmiley = function() {
	"use strict";
	
	var _index = 0;
	
	return {
		init: function() {
			require(['EventHandler'], (function(EventHandler) {
				EventHandler.add('com.woltlab.wcf.redactor2', 'insertSmiley_' + this.$element[0].id, this.WoltLabSmiley._insert.bind(this));
			}).bind(this));
		},
		
		_insert: function(data) {
			if (this.WoltLabSource.isActive()) {
				return;
			}
			
			this.buffer.set();
			
			var id = 'wscSmiley_' + this.uuid + '_' + _index++;
			
			var smiley = data.img.cloneNode();
			smiley.id = id;
			this.insert.html(smiley.outerHTML);
			
			// Firefox and Safari tend to ignore the `srcset` attribute, all though
			// it is clearly present in the DOM. Overwriting the element with itself
			// is somehow fixing that issue, yay!
			smiley = elById(id);
			smiley.removeAttribute('id');
			
			smiley.parentNode.insertBefore(document.createTextNode(" "), smiley);
			
			const whitespace = document.createTextNode(" ");
			smiley.parentNode.insertBefore(whitespace, smiley.nextSibling);
			
			// Replace the image with itself to forcefully invalidate any references.
			//noinspection SillyAssignmentJS
			smiley.outerHTML = smiley.outerHTML;

			const selection = window.getSelection();
			const range = document.createRange();
			range.selectNode(whitespace);
			range.collapse(false);

			selection.removeAllRanges();
			selection.addRange(range);
			
			// force-save the caret position
			this.WoltLabCaret.forceSelectionSave();
		}
	}
};
