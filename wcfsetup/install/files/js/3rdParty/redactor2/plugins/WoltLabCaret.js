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
			
			this.WoltLabCaret._initInternalRange();
		},
		
		endOfEditor: function () {
			var editor = this.core.editor()[0];
			
			if (document.activeElement !== editor) {
				editor.focus();
			}
			
			var lastElement = editor.lastElementChild;
			if (lastElement.nodeName === 'P') {
				this.caret.end(lastElement);
			}
			else {
				this.caret.after(lastElement);
			}
		},
		
		_initInternalRange: function () {
			var editor = this.core.editor()[0];
			var internalRange = null;
			var selection = window.getSelection();
			
			var saveRange = function () {
				internalRange = (selection.rangeCount) ? selection.getRangeAt(0).cloneRange() : null;
			};
			
			var restoreRange = function () {
				if (internalRange === null) return;
				
				if (document.activeElement === editor) {
					var range = selection.getRangeAt(0);
					if (range.startOffset !== 0) {
						return;
					}
					
					var node = range.startContainer;
					while (node) {
						if (node.parentNode === editor) {
							if (node.previousSibling) {
								return;
							}
							
							break;
						}
						
						if (node.previousSibling) {
							return;
						}
						
						node = node.parentNode;
					}
					
					if (!node) return;
				}
				
				editor.focus();
				
				selection.removeAllRanges();
				selection.addRange(internalRange);
				
				internalRange = null;
			};
			
			editor.addEventListener('keyup', saveRange);
			editor.addEventListener('mouseup', function () {
				if (selection.rangeCount) {
					saveRange();
				}
			});
			
			var mpSave = this.selection.save;
			this.selection.save = (function () {
				internalRange = null;
				
				mpSave.call(this);
			}).bind(this);
			
			var mpRestore = this.selection.restore;
			this.selection.restore = (function () {
				if (internalRange) {
					restoreRange();
					
					if (selection.rangeCount && this.utils.isRedactorParent(selection.getRangeAt(0).commonAncestorContainer)) {
						return;
					}
				}
				
				mpRestore.call(this);
			}).bind(this);
			
			var mpSet = this.buffer.set;
			this.buffer.set = (function (type) {
				if (document.activeElement !== editor) {
					restoreRange();
				}
				
				mpSet.call(this, type);
				
				saveRange();
			}).bind(this);
			
			var mpHtml = this.insert.html;
			this.insert.html = (function (html, data) {
				mpHtml.call(this, html, data);
				
				saveRange();
			}).bind(this);
			
			require(['Environment'], (function (Environment) {
				if (Environment.platform() === 'ios') {
					editor.addEventListener('focus', function () {
						document.addEventListener('selectionchange', saveRange);
					});
					
					editor.addEventListener('blur', function () {
						document.removeEventListener('selectionchange', saveRange);
					})
				}
			}).bind(this));
		},
		
		_handleEditorClick: function (event) {
			if (!this.selection.get().isCollapsed) {
				return;
			}
			
			var block = this.selection.block();
			if (block === false) {
				return;
			}
			
			// get block element that received the click
			var targetBlock = event.target;
			while (targetBlock && !this.utils.isBlockTag(targetBlock.nodeName)) {
				targetBlock = targetBlock.parentNode;
			}
			
			if (!targetBlock || targetBlock === block) {
				return;
			}
			
			if (block.nodeName === 'P') {
				block = block.parentNode;
				if (block === this.$editor[0] || !this.utils.isBlockTag(block.nodeName)) {
					return;
				}
			}
			
			if (block.nodeName === 'TD') {
				while (block.nodeName !== 'TABLE') {
					block = block.parentNode;
				}
			}
			
			// ignore headlines
			if (block.nodeName.match(/^H\d$/)) {
				return;
			}
			
			// handle nested blocks
			var insertBefore, rect;
			var parent = block;
			while (parent) {
				rect = parent.getBoundingClientRect();
				
				if (event.clientY < rect.top) {
					insertBefore = true;
					block = parent;
				}
				else if (event.clientY > rect.bottom) {
					insertBefore = false;
					block = parent;
				}
				else {
					break;
				}
				
				if (parent.parentNode && parent.parentNode !== this.$editor[0]) {
					parent = parent.parentNode;
				}
				else {
					break;
				}
			}
			
			// check if there is already a paragraph in place
			var sibling = block[(insertBefore ? 'previous' : 'next') + 'ElementSibling'];
			if (sibling && sibling.nodeName === 'P') {
				this.caret.end(sibling);
				
				return;
			}
			
			this.buffer.set();
			
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
