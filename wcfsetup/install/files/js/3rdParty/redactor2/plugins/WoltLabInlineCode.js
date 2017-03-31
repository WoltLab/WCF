$.Redactor.prototype.WoltLabInlineCode = function() {
	"use strict";
	
	var _environment;
	
	return {
		init: function() {
			this.opts.activeButtonsStates.kbd = 'tt';
			
			require(['Environment', 'EventHandler'], (function (Environment, EventHandler) {
				_environment = Environment;
				
				EventHandler.add('com.woltlab.wcf.redactor2', 'bbcode_tt_' + this.$element[0].id, this.WoltLabInlineCode._toggle.bind(this));
			}).bind(this));
		},
		
		_toggle: function (data) {
			data.cancel = true;
			
			this.button.toggle({}, 'kbd', 'func', 'inline.format');
			
			var selection = window.getSelection();
			var node = selection.anchorNode;
			if (node.nodeType === Node.TEXT_NODE) node = node.parentNode;
			
			if (node.nodeName === 'KBD') {
				var nextSibling = node.nextSibling;
				
				if (_environment.platform() === 'ios' && _environment.browser() === 'safari') {
					// iOS Safari work-around to allow caret placement after <kbd>
					if (nextSibling && nextSibling.nodeName === 'BR') {
						node.parentNode.insertBefore(document.createTextNode('\u200B'), nextSibling);
					}
					
					// fix selection position
					if (node.innerHTML === '\u200B') {
						// the caret must be at offset 0 (before the whitespace)
						var range = selection.getRangeAt(0);
						if (range.collapsed && range.startOffset === 1) {
							node.innerHTML = this.marker.html() + '\u200B';
							
							this.selection.restore();
						}
					}
					
					return;
				}
				
				while (nextSibling) {
					if (nextSibling.nodeType !== Node.TEXT_NODE || nextSibling.textContent.length) {
						return;
					}
					
					nextSibling = nextSibling.nextSibling;
				}
				
				if (nextSibling) {
					nextSibling.textContent = '\u200B';
					console.log("this");
				}
				else {
					node.parentNode.appendChild(document.createTextNode('\u200B'));
					console.log("this");
				}
			}
		}
	};
};
