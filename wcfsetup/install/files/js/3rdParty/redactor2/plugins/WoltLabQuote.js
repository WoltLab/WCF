$.Redactor.prototype.WoltLabQuote = function() {
	"use strict";
	
	return {
		init: function() {
			// TODO: this should be somewhere else
			var button = this.button.add('woltlabQuote', '');
			this.button.addCallback(button, this.WoltLabQuote.insert);
			
			require(['WoltLab/WCF/Ui/Redactor/Quote'], (function(UiRedactorQuote) {
				UiRedactorQuote.initEditor(this.$element[0].id, this.$editor[0]);
			}).bind(this));
		},
		
		insert: function() {
			require(['Dom/Traverse', 'WoltLab/WCF/Ui/Redactor/Quote'], (function(DomTraverse, UiRedactorQuote) {
				var current = this.selection.current();
				if (current) {
					if (current.nodeType === Node.TEXT_NODE) current = current.parentNode;
					
					if (current.nodeName === 'BLOCKQUOTE' || DomTraverse.parentByTag(current, 'BLOCKQUOTE', this.$editor[0])) {
						return;
					}
				}
				
				UiRedactorQuote.insert((function(element) {
					element.innerHTML = this.opts.invisibleSpace + this.selection.markerHtml();
					
					this.insert.html(element.outerHTML);
				}).bind(this));
			}).bind(this));
		}
	};
};
