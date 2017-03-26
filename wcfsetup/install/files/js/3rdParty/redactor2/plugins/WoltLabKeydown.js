$.Redactor.prototype.WoltLabKeydown = function() {
	"use strict";
	
	var _tags = [];
	
	return {
		init: function () {
			var mpInit = this.keydown.init;
			this.keydown.init = (function (e) {
				var returnValue = mpInit.call(this, e);
				
				if (returnValue !== false && !e.originalEvent.defaultPrevented) {
					e = e.originalEvent;
					
					// 39 == right
					if (e.which === 39 && !e.ctrlKey && !e.shiftKey && !e.metaKey && !e.altKey) {
						var selection = window.getSelection();
						if (!selection.isCollapsed) {
							return;
						}
						
						var current = selection.anchorNode;
						if (current.nodeType !== Node.TEXT_NODE || selection.getRangeAt(0).startOffset !== current.textContent.length) {
							return;
						}
						
						var parent = current.parentNode;
						if (parent.nodeName !== 'KBD') {
							return;
						}
						
						// check if caret is at the very end
						
						// check if there is absolutely nothing afterwards
						var isAtTheVeryEnd = true;
						var node = parent;
						while (node && node !== this.core.editor()[0]) {
							if (node.nextSibling !== null) {
								// strip empty text nodes
								while (node.nextSibling && node.nextSibling.nodeType === Node.TEXT_NODE && node.nextSibling.textContent.length === 0) {
									node.parentNode.removeChild(node.nextSibling);
								}
								
								if (node.nextSibling && node.nextSibling.nodeName !== 'BR' || node.nextSibling.nextSibling !== null) {
									isAtTheVeryEnd = false;
									break;
								}
							}
							
							node = node.parentNode;
						}
						
						if (isAtTheVeryEnd) {
							parent.parentNode.insertBefore(document.createTextNode('\u200B'), parent.nextSibling);
						}
					}
				}
			}).bind(this);
			
			var ua = window.navigator.userAgent.toLowerCase();
			if (ua.indexOf('linux') !== -1 && ua.indexOf('android') !== -1 && ua.indexOf('chrome') !== -1) {
				// prevent the word duplication issue on Chrome for Android,
				// caused by the call to buffer.set() during backspace
				this.keydown.checkEvents = (function() {
					// also discard the previous existing click event
					this.core.addEvent(false);
				}).bind(this);
			}
			
			// rebind keydown event
			this.core.editor().off('keydown.redactor');
			this.core.editor().on('keydown.redactor', this.keydown.init.bind(this));
			
			this.keydown.onArrowDown = (function() {
				var tags = this.WoltLabKeydown._getBlocks();
				
				for (var i = 0; i < tags.length; i++) {
					if (tags[i]) {
						this.keydown.insertAfterLastElement(tags[i]);
						return false;
					}
				}
			}).bind(this);
			
			this.keydown.onArrowUp = (function() {
				var tags = this.WoltLabKeydown._getBlocks();
				
				for (var i = 0; i < tags.length; i++) {
					if (tags[i]) {
						this.keydown.insertBeforeFirstElement(tags[i]);
						return false;
					}
				}
			}).bind(this);
			
			var mpOnEnter = (function(e) {
				var stop = this.core.callback('enter', e);
				if (stop === false) {
					e.preventDefault();
					return false;
				}
				
				// blockquote exit
				if (this.keydown.blockquote && this.keydown.exitFromBlockquote(e) === true) {
					return false;
				}
				
				// pre
				if (this.keydown.pre) {
					return this.keydown.insertNewLine(e);
				}
				// blockquote & figcaption
				else if (this.keydown.blockquote || this.keydown.figcaption) {
					return this.keydown.insertBreakLine(e);
				}
				// figure
				else if (this.keydown.figure) {
					setTimeout($.proxy(function () {
						this.keydown.replaceToParagraph('FIGURE');
						
					}, this), 1);
				}
				// paragraphs
				else if (this.keydown.block) {
					setTimeout($.proxy(function () {
						this.keydown.replaceToParagraph('DIV');
						
					}, this), 1);
					
					// empty list exit
					if (this.keydown.block.tagName === 'LI') {
						var current = this.selection.current();
						var $parent = $(current).closest('li', this.$editor[0]);
						// WoltLab modification: this was a call to $.parents() that did
						// escape Redactor
						var $list = $parent.parentsUntil(this.$editor[0], 'ul,ol').last();
						
						if ($parent.length !== 0 && this.utils.isEmpty($parent.html()) && $list.next().length === 0 && this.utils.isEmpty($list.find("li").last().html())) {
							$list.find("li").last().remove();
							
							var node = $(this.opts.emptyHtml);
							$list.after(node);
							this.caret.start(node);
							
							return false;
						}
					}
					
				}
				// outside
				else if (!this.keydown.block) {
					return this.keydown.insertParagraph(e);
				}
				
				// firefox enter into inline element
				if (this.detect.isFirefox() && this.utils.isInline(this.keydown.parent)) {
					this.keydown.insertBreakLine(e);
					
					// WoltLab modification: strip links on return
					setTimeout((function () {
						var block = this.selection.block();
						var current = this.selection.inline();
						
						while (current && current !== block) {
							if (current.nodeName === 'A') {
								// check if this is an empty link
								var remove = false;
								if (current.childNodes.length === 0) {
									remove = true;
								}
								else if (current.textContent.replace(/\u200B/g, '') === '') {
									remove = true;
									
									// check if there are only <span> elements
									elBySelAll('*', current, function (element) {
										if (element.nodeName !== 'SPAN') {
											remove = false;
										}
									});
								}
								
								if (remove) {
									while (current.childNodes.length) {
										current.parentNode.insertBefore(current.childNodes[0], current);
									}
									
									elRemove(current);
									
									break;
								}
							}
							
							current = current.parentNode;
						}
					}).bind(this), 1);
					
					return;
				}
				
				// remove inline tags in new-empty paragraph
				/*
					WoltLab modification: preserve inline tags
				
				setTimeout($.proxy(function () {
					var inline = this.selection.inline();
					if (inline && this.utils.isEmpty(inline.innerHTML)) {
						var parent = this.selection.block();
						$(inline).remove();
						//this.caret.start(parent);
						
						var range = document.createRange();
						range.setStart(parent, 0);
						
						var textNode = document.createTextNode('\u200B');
						
						range.insertNode(textNode);
						range.setStartAfter(textNode);
						range.collapse(true);
						
						var sel = window.getSelection();
						sel.removeAllRanges();
						sel.addRange(range);
					}
					
				}, this), 1);
				*/
			}).bind(this);
			
			this.keydown.onEnter = (function(e) {
				var isBlockquote = this.keydown.blockquote;
				if (isBlockquote) this.keydown.blockquote = false;
				
				var returnValue = mpOnEnter.call(this, e);
				
				if (isBlockquote) this.keydown.blockquote = isBlockquote;
				
				return returnValue;
			}).bind(this);
			
			this.keydown.replaceToParagraph = (function(tag) {
				var blockElem = this.selection.block();
				
				var blockHtml = blockElem.innerHTML.replace(/<br\s?\/?>/gi, '');
				if (blockElem.tagName === tag && this.utils.isEmpty(blockHtml) && !$(blockElem).hasClass('redactor-in'))
				{
					var p = document.createElement('p');
					$(blockElem).replaceWith(p);
					
					// caret to p
					var range = document.createRange();
					range.setStart(p, 0);
					
					var textNode = document.createTextNode('\u200B');
					
					range.insertNode(textNode);
					range.setStartAfter(textNode);
					range.collapse(true);
					
					var sel = window.getSelection();
					sel.removeAllRanges();
					sel.addRange(range);
					
					return false;
				}
				else if (blockElem.tagName === 'P')
				{
					// WoltLab modification: do not remove class, preserving
					// text alignment
					$(blockElem)/*.removeAttr('class')*/.removeAttr('style');
				}
			}).bind(this);
			
			this.keydown.onShiftEnter = (function(e) {
				this.buffer.set();
				
				if (this.keydown.pre) {
					return this.keydown.insertNewLine(e);
				}
				
				return this.insert.raw('<br>\u200B');
			}).bind(this);
			
			var mpOnTab = this.keydown.onTab;
			this.keydown.onTab = (function(e, key) {
				if (!this.keydown.pre && $(this.selection.current()).closest('ul, ol', this.core.editor()[0]).length === 0) {
					return true;
				}
				
				return mpOnTab.call(this, e, key);
			}).bind(this);
			
			var mpFormatEmpty = this.keydown.formatEmpty;
			this.keydown.formatEmpty = (function (e) {
				// check if there are block elements other than <p>
				var editor = this.$editor[0], node;
				for (var i = 0, length = editor.childElementCount; i < length; i++) {
					node = editor.children[i];
					if (node.nodeName !== 'P' && this.utils.isBlockTag(node.nodeName)) {
						// there is at least one block element, treat as non-empty
						return;
					}
				}
				
				return mpFormatEmpty.call(this, e);
			}).bind(this);
			
			require(['Core', 'Environment'], (function (Core, Environment) {
				if (Environment.platform() !== 'desktop') {
					// ignore mobile devices
					return;
				}
				
				var container = this.$editor[0].closest('form, .message');
				if (container === null) return;
				
				var formSubmit = elBySel('.formSubmit', container);
				if (formSubmit === null) return;
				
				var submitButton = elBySel('input[type="submit"], button[data-type="save"], button[accesskey="s"]', formSubmit);
				if (submitButton) {
					// remove access key
					submitButton.removeAttribute('accesskey');
					
					// mimic the same behavior which will also work with more
					// than one editor instance on the page
					this.WoltLabEvent.register('keydown', (function (data) {
						// 83 = [S]
						if (data.event.which === 83) {
							var submit = false;
							
							if (window.navigator.platform.match(/^Mac/)) {
								if (data.event.ctrlKey && data.event.altKey) {
									submit = true;
								}
							}
							else if (data.event.altKey && !data.event.ctrlKey) {
								submit = true;
							}
							
							if (submit) {
								data.cancel = true;
								
								if (typeof submitButton.click === 'function') {
									submitButton.click();
								}
								else {
									Core.triggerEvent(submitButton, WCF_CLICK_EVENT);
								}
							}
						}
					}).bind(this));
				}
			}).bind(this));
			
			this.WoltLabKeydown._handleBackspaceAndDelete();
		},
		
		register: function (tag) {
			if (_tags.indexOf(tag) === -1) {
				_tags.push(tag);
			}
		},
		
		_getBlocks: function () {
			var tags = [this.keydown.blockquote, this.keydown.pre, this.keydown.figcaption];
			
			for (var i = 0, length = _tags.length; i < length; i++) {
				tags.push(this.utils.isTag(this.keydown.current, _tags[i]))
			}
			
			return tags;
		},
		
		_handleBackspaceAndDelete: function () {
			var isEmpty = function(element) {
				return (elBySel('img', element) === null && element.textContent.replace(/\u200B/g, '').trim() === '');
			};
			
			var firefoxHandleBackspace = (function(e) {
				var parent;
				var block = this.selection.block();
				if (!block) {
					return;
				}
				
				if (block.nodeName === 'TD') {
					var html = block.innerHTML;
					if (html === '\u200B') {
						// backspacing the `\u200B` will break Firefox
						e.preventDefault();
					}
					else if (html === '') {
						// Firefox already broke itself, try to recover
						e.preventDefault();
						
						block.innerHTML = '\u200B';
					}
				}
				else if (block.nodeName.indexOf('-') !== -1 && isEmpty(block)) {
					// backspacing an entire block
					parent = block.parentNode;
					parent.insertBefore(this.marker.get(), block.nextSibling);
					
					elRemove(block);
					
					this.selection.restore();
				}
				else {
					parent = (block && block.nodeName === 'P') ? block.parentNode : null;
					if (parent && parent.nodeName.indexOf('-') !== -1) {
						var orgRange = window.getSelection().getRangeAt(0);
						
						// check if there is anything in front of the caret
						var range = document.createRange();
						range.setStartBefore(block);
						range.setEnd(orgRange.startContainer, orgRange.startOffset);
						
						var fragment = range.cloneContents();
						var div = elCreate('div');
						div.appendChild(fragment);
						
						// caret is at start
						if (isEmpty(div)) {
							// prevent Firefox from giving the DOM a good beating 
							e.preventDefault();
							
							var sibling = block.previousElementSibling;
							// can be `true` to remove, `false` to merge and `null` to do nothing
							var removeSibling = null;
							if (sibling) {
								removeSibling = (isEmpty(sibling));
							}
							else {
								parent = block;
								while (parent = parent.parentNode) {
									if (parent === this.$editor[0]) {
										break;
									}
									
									sibling = parent.previousElementSibling;
									if (sibling) {
										// setting to false triggers the merge
										removeSibling = false;
										break;
									}
								}
							}
							
							if (removeSibling) {
								elRemove(sibling);
							}
							else if (removeSibling !== null) {
								var oldParent = block.parentNode;
								
								// merge blocks
								if (sibling.nodeName === 'P') {
									sibling.appendChild(this.marker.get());
									
									while (block.childNodes.length) {
										sibling.appendChild(block.childNodes[0]);
									}
									
									elRemove(block);
									this.selection.restore();
								}
								else {
									sibling.appendChild(block);
									
									block.insertBefore(this.marker.get(), block.firstChild);
									this.selection.restore();
								}
								
								// check if `parent` is now completely empty
								if (isEmpty(oldParent)) {
									elRemove(oldParent);
								}
							}
							else if (removeSibling === null) {
								// check if the parent is empty and the user wants to remove the parent
								parent = block.parentNode;
								if (isEmpty(parent)) {
									elRemove(parent);
								}
							}
						}
					}
				}
			}).bind(this);
			
			var firefoxHandleDelete = (function(e) {
				var parent;
				var block = this.selection.block();
				if (block.nodeName.indexOf('-') !== -1 && isEmpty(block)) {
					// deleting an entire block
					parent = block.parentNode;
					parent.insertBefore(this.marker.get(), block.nextSibling);
					
					elRemove(block);
					
					this.selection.restore();
				}
				else {
					parent = (block && block.nodeName === 'P') ? block.parentNode : null;
					if (parent && parent.nodeName.indexOf('-') !== -1) {
						var orgRange = window.getSelection().getRangeAt(0);
						
						// check if there is anything after the caret
						var range = document.createRange();
						range.setStart(orgRange.startContainer, orgRange.startOffset);
						range.setEndAfter(block);
						
						var fragment = range.cloneContents();
						var div = elCreate('div');
						div.appendChild(fragment);
						
						// caret is at end
						if (isEmpty(div)) {
							// prevent Firefox from giving the DOM a good beating 
							e.preventDefault();
							
							var sibling = block.nextElementSibling;
							// can be `true` to remove, `false` to merge and `null` to do nothing
							var removeSibling = null;
							if (sibling) {
								removeSibling = (isEmpty(sibling));
							}
							else {
								parent = block;
								while (parent = parent.parentNode) {
									if (parent === this.$editor[0]) {
										break;
									}
									
									sibling = parent.nextElementSibling;
									if (sibling) {
										// setting to false triggers the merge
										removeSibling = false;
										break;
									}
								}
							}
							
							if (removeSibling) {
								elRemove(sibling);
							}
							else if (removeSibling !== null) {
								var oldParent = sibling.parentNode;
								
								// merge blocks
								if (sibling.nodeName === 'P') {
									while (sibling.childNodes.length) {
										block.appendChild(sibling.childNodes[0]);
									}
									
									elRemove(sibling);
								}
								else {
									block.appendChild(this.marker.get());
									
									parent = block.parentNode;
									if (sibling.nodeName.indexOf('-') !== -1) {
										var content = sibling.firstElementChild;
										if (content && content.nodeName === 'P') {
											while (content.childNodes.length) {
												block.appendChild(content.childNodes[0]);
											}
											
											sibling.removeChild(content);
											
											if (isEmpty(sibling)) {
												elRemove(sibling);
											}
										}
									}
									else {
										parent.insertBefore(sibling, block.nextSibling);
									}
									
									this.selection.restore();
								}
								
								// check if `parent` is now completely empty
								if (isEmpty(oldParent)) {
									elRemove(oldParent);
								}
							}
							else if (removeSibling === null) {
								// check if the parent is empty and the user wants to remove the parent
								parent = block.parentNode;
								if (isEmpty(parent)) {
									elRemove(parent);
								}
							}
						}
					}
				}
			}).bind(this);
			
			this.keydown.onBackspaceAndDeleteAfter = (function (e) {
				//noinspection JSValidateTypes
				if (this.detect.isFirefox() && this.selection.isCollapsed()) {
					if (e.which === this.keyCode.BACKSPACE) {
						firefoxHandleBackspace(e);
					}
					else if (e.which === this.keyCode.DELETE) {
						firefoxHandleDelete(e);
					}
				}
				
				// remove style tag
				setTimeout($.proxy(function()
				{
					this.code.syncFire = false;
					this.keydown.removeEmptyLists();
					
					// WoltLab modification: allow style tag on `<span>`
					this.core.editor().find('*[style]').not('span, img, #redactor-image-box, #redactor-image-editter').removeAttr('style');
					
					this.keydown.formatEmpty(e);
					
					// strip empty <kbd>
					var current = this.selection.current();
					if (current.nodeName === 'KBD' && current.innerHTML.length === 0) {
						elRemove(current);
					}
					
					this.code.syncFire = true;
					
				}, this), 1);
			}).bind(this);
		}
	}
};
