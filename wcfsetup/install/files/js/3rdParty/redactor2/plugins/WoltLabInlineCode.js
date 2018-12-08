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
			var node;
			
			data.cancel = true;
			
			var selection = window.getSelection();
			
			// check if the caret is at the front position of a non-empty `<kbd>`
			if (selection.isCollapsed) {
				node = null;
				
				var kbd = selection.anchorNode;
				if (kbd.nodeType === Node.TEXT_NODE) kbd = kbd.parentNode;
				
				var isAtStart = false;
				if (kbd.nodeName === 'KBD' && kbd.textContent.replace(/\u200b/g, '') !== '') {
					var anchorNode = selection.anchorNode;
					var anchorOffset = selection.anchorOffset;
					if (anchorNode.nodeType === Node.TEXT_NODE && anchorOffset === 0) {
						node = anchorNode;
					}
					else if (anchorNode === kbd) {
						if (anchorOffset === 0) {
							isAtStart = true;
						}
						else {
							node = kbd.childNodes[anchorOffset - 1];
						}
					}
				}
				
				if (isAtStart === false && node !== null) {
					var childNode;
					for (var i = 0, length = kbd.childNodes.length; i < length; i++) {
						childNode = kbd.childNodes[i];
						if (childNode === node) {
							isAtStart = true;
							break;
						}
						else if (childNode.nodeType !== Node.TEXT_NODE || childNode.textContent.replace(/\u200b/g, '') !== '') {
							break;
						}
					}
				}
				
				if (isAtStart) {
					var sibling = kbd.previousSibling;
					if (sibling === null || sibling.nodeType !== Node.TEXT_NODE || sibling.textContent !== '\u200b') {
						sibling = document.createTextNode('\u200b');
						kbd.parentNode.insertBefore(sibling, kbd);
					}
					
					this.caret.before(kbd);
					return;
				}
			}
			
			this.button.toggle({}, 'kbd', 'func', 'inline.format');
			
			node = selection.anchorNode;
			if (node.nodeType === Node.TEXT_NODE) node = node.parentNode;
			
			if (node.nodeName === 'KBD') {
				var nextSibling = node.nextSibling;
				
				if (_environment.platform() === 'ios' && _environment.browser() === 'safari') {
					// iOS Safari work-around to allow caret placement after <kbd>
					if (nextSibling && nextSibling.nodeName === 'BR') {
						node.parentNode.insertBefore(document.createTextNode('\u200B'), nextSibling);
					}
					
					// fix selection position
					/*if (node.innerHTML === '\u200B') {
						// the caret must be at offset 0 (before the whitespace)
						var range = selection.getRangeAt(0);
						if (range.collapsed && range.startOffset === 1) {
							node.innerHTML = this.marker.html() + '\u200B';
							
							this.selection.restore();
						}
					}*/
					
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
				}
				else {
					node.parentNode.appendChild(document.createTextNode('\u200B'));
				}
			}
		}
	};
};
