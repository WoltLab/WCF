$.Redactor.prototype.WoltLabInlineCode = function() {
	"use strict";
	
	return {
		init: function() {
			this.opts.activeButtonsStates.kbd = 'tt';
			
			require(['EventHandler'], (function (EventHandler) {
				EventHandler.add('com.woltlab.wcf.redactor2', 'bbcode_tt_' + this.$element[0].id, this.WoltLabInlineCode._toggle.bind(this));
			}).bind(this));
		},
		
		_toggle: function (data) {
			data.cancel = true;
			
			this.button.toggle({}, 'kbd', 'func', 'inline.format');
			
			var node = window.getSelection().anchorNode;
			if (node.nodeType === Node.TEXT_NODE) node = node.parentNode;
			
			if (node.nodeName === 'KBD') {
				var nextSibling = node.nextSibling;
				while (nextSibling) {
					if (nextSibling.nodeType !== Node.TEXT_NODE || nextSibling.textContent.length) {
						return;
					}
					
					nextSibling = nextSibling.nextSibling;
				}
				
				if (nextSibling) {
					nextSibling.textContent = '\u200B';
				}
				else {
					node.parentNode.appendChild(document.createTextNode('\u200B'));
				}
			}
		}
	};
};
