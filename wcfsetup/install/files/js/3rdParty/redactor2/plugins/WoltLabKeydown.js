$.Redactor.prototype.WoltLabKeydown = function() {
	"use strict";
	
	var _isSafari = false;
	var _tags = [];
	
	return {
		init: function () {
			var selection = window.getSelection();

			require(['Environment'], function (Environment) {
				_isSafari = (Environment.browser() === 'safari');
			});
			
			var mpInit = this.keydown.init;
			this.keydown.init = (function (e) {
				var node;
				
				// remove empty whitespaces in front of an <img> when backspacing in Firefox
				if (this.detect.isFirefox() && selection.isCollapsed && e.which === this.keyCode.BACKSPACE) {
					node = selection.anchorNode;
					if (node.nodeType === Node.ELEMENT_NODE && selection.anchorOffset > 0) {
						node = node.childNodes[selection.anchorOffset - 1];
					}
					
					if (node.nodeType === Node.TEXT_NODE && node.textContent === '\u200B') {
						var emptyNodes = [];
						var sibling = node;
						while (sibling = sibling.previousSibling) {
							if (sibling.nodeType === Node.ELEMENT_NODE) {
								if (sibling.nodeName !== 'IMG') emptyNodes = [];
								
								break;
							}
							else if (sibling.nodeType === Node.TEXT_NODE) {
								var text = sibling.textContent;
								if (text === '' || text === '\u200B') {
									emptyNodes.push(sibling);
								}
								else {
									emptyNodes = [];
									break;
								}
							}
						}
						
						if (emptyNodes.length) {
							emptyNodes.forEach(elRemove);
						}
					}
				}
				
				// delete the current line on backspace and delete, if it is empty, and move
				// the caret into the adjacent element, rather than pulling content out
				if (e.originalEvent.which === this.keyCode.BACKSPACE || e.originalEvent.which === this.keyCode.DELETE) {
					if (selection.isCollapsed) {
						var container = this.selection.block();
						if (container.nodeName === 'P') {
							// check if we're merging "adjacent" lists
							if (this.list.combineAfterAndBefore(container)) {
								e.originalEvent.preventDefault();
								return;
							}
							else if (this.utils.isEmpty(container.innerHTML)) {
								// simple comparison to check that at least one sibling is not null
								if (container.previousElementSibling !== container.nextElementSibling) {
									var caretEnd = null, caretStart = null;
									
									if (e.originalEvent.which === this.keyCode.BACKSPACE) {
										if (container.previousElementSibling === null) {
											caretStart = container.nextElementSibling;
										}
										else {
											caretEnd = container.previousElementSibling;
										}
									}
									else {
										if (container.nextElementSibling === null) {
											caretEnd = container.previousElementSibling;
										}
										else {
											caretStart = container.nextElementSibling;
										}
									}
									
									elRemove(container);
									
									if (caretStart === null) {
										if (caretEnd.nodeName === 'OL' || caretEnd.nodeName === 'UL') {
											caretEnd = caretEnd.lastElementChild;
										}
										
										this.caret.end(caretEnd);
									}
									else {
										if (caretStart.nodeName === 'OL' || caretStart.nodeName === 'UL') {
											caretStart = caretStart.firstElementChild;
										}
										
										this.caret.start(caretStart);
									}
									
									e.originalEvent.preventDefault();
									return;
								}
							}
						}
						else if (e.originalEvent.which === this.keyCode.DELETE && ['H1', 'H2', 'H3', 'H4', 'H5', 'H6'].indexOf(container.nodeName) !== -1 && this.utils.isEmpty(container.innerHTML)) {
							// Pressing the [DEL] key inside an empty heading should remove the heading entirely, instead
							// of moving the next element's content into it. There are two situations that need to be handled:
							// 
							//   1. There is adjacent content, remove the heading and set the caret at the beginning of it.
							//   2. The heading is the last element, replace it with a `<p>` and move the caret inside of it.
							var nextElement = container.nextElementSibling;
							if (nextElement) {
								nextElement = container.nextElementSibling;
							}
							else {
								nextElement = elCreate('p');
								nextElement.innerHTML = this.opts.invisibleSpace;
								container.parentNode.appendChild(nextElement);
							}
							
							container.parentNode.removeChild(container);
							this.caret.start(nextElement);
							
							e.originalEvent.preventDefault();
							return;
						}
						else if (this.detect.isWebkit() && container.nodeName === 'LI' && e.which === this.keyCode.DELETE) {
							// check if caret is at the end of the list item, and if there is an adjacent list item
							var anchorNode = selection.anchorNode;
							if (anchorNode.nodeType === Node.TEXT_NODE && anchorNode.textContent.length === selection.anchorOffset && anchorNode.parentNode.lastChild === anchorNode) {
								var nextItem = container.nextElementSibling;
								if (nextItem && nextItem.nodeName === 'LI') {
									this.buffer.set();
									
									this.selection.save();
									
									while (nextItem.childNodes.length) {
										container.appendChild(nextItem.childNodes[0]);
									}
									elRemove(nextItem);
									
									this.selection.restore();
									
									e.preventDefault();
									return;
								}
							}
						}
					}
				}
				
				// Redactor's own work-around for backspacing in Firefox at the start of a block
				// is flawed when the previous element is a list. Their current implementation
				// inserts the content straight into the list element, rather than appending it
				// to the last possible location inside a <li>.
				var br = null;
				if (e.which === this.keyCode.BACKSPACE && this.detect.isFirefox()) {
					var block = this.selection.block();
					if (block && block.tagName === 'P' && this.utils.isStartOfElement(block)) {
						var previousBlock = block.previousElementSibling;
						if (previousBlock && (previousBlock.nodeName === 'OL' || previousBlock.nodeName === 'UL')) {
							this.buffer.set();
							this.selection.save();
							
							var listItem = previousBlock.lastElementChild;
							while (block.childNodes.length) {
								listItem.appendChild(block.childNodes[0]);
							}
							
							elRemove(block);
							this.selection.restore();
							
							e.preventDefault();
							return;
						}
						else if (previousBlock && previousBlock.nodeName === 'P') {
							// Firefox moves the <br> of a previous <p><br></p> into the current container instead of removing the <br> along with the <p>.
							br = previousBlock.lastElementChild;
							if (br !== null && br.nodeName !== 'BR') {
								br = null;
							}
						}
					}
				}
				
				var returnValue = mpInit.call(this, e);
				
				if (returnValue !== false && !e.originalEvent.defaultPrevented) {
					e = e.originalEvent;
					
					// 39 == right
					if (e.which === 39 && !e.ctrlKey && !e.shiftKey && !e.metaKey && !e.altKey) {
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
						node = parent;
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
				else if (br !== null && this.detect.isFirefox()) {
					var range = selection.getRangeAt(0);
					if (range.startOffset === 1 && range.startContainer.firstElementChild === br) {
						elRemove(br);
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
				var next, tag, tags = this.WoltLabKeydown._getBlocks();
				
				for (var i = 0; i < tags.length; i++) {
					tag = tags[i];
					if (tag) {
						if (!this.utils.isEndOfElement(tag)) {
							continue;
						}
						
						next = tag.nextElementSibling;
						if (next !== null && next.nodeName === 'P') {
							break;
						}
						
						this.keydown.insertAfterLastElement(tag);
						return;
					}
				}
			}).bind(this);
			
			this.keydown.onArrowUp = (function() {
				var previous, tag, tags = this.WoltLabKeydown._getBlocks();
				
				for (var i = 0; i < tags.length; i++) {
					tag = tags[i];
					if (tag) {
						if (!this.utils.isStartOfElement()) {
							break;
						}
						
						previous = tag.previousElementSibling;
						if (previous !== null && previous.nodeName !== 'P') {
							var p = $(this.opts.emptyHtml)[0];
							tag.parentNode.insertBefore(p, tag);
							
							this.caret.end(p);
							break;
						}
						
						this.keydown.insertBeforeFirstElement(tag);
						return;
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
						// WoltLab modification: own list handling
						var current = this.selection.current();
						var listItem = elClosest(current, 'li');
						
						// We want to offload as much as possible to the browser, which already
						// includes a handling of the enter key in an empty list item. Unfortunately,
						// they do not recognize this at all times, in particular certain white-spaces
						// and <br> may not always play well.
						if (this.utils.isRedactorParent(listItem) && this.utils.isEmpty(listItem.innerHTML)) {
							// The current item is empty and there is no adjacent one, force clear the
							// contents to enable browser recognition.
							// 
							// Unless this is Safari, which absolutely loves empty lines containing only
							// a `<br>` and freaks out when a block element is completely empty.
							if (listItem.nextElementSibling === null) {
								listItem.innerHTML = (_isSafari) ? '<br>' : '';
								
								// If this is Safari, we'll move the caret behind the `<br>`, otherwise
								// nothing will happen.
								if (_isSafari) {
									var range = document.createRange();
									range.selectNodeContents(listItem);
									range.collapse(false);
									
									var selection = window.getSelection();
									selection.removeAllRanges();
									selection.addRange(range);
								}
							}
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
								else if (current.textContent.replace(/\u200B/g, '').trim() === '') {
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
				if (!this.keydown.pre) {
					var closestRelevantBlock = $(this.selection.current()).closest('ul, ol, td', this.core.editor()[0]);
					if (closestRelevantBlock.length === 0) {
						// ignore tab, the browser's default action will be executed
						return true;
					}
					
					closestRelevantBlock = closestRelevantBlock[0];
					if (closestRelevantBlock.nodeName === 'TD') {
						var target = null;
						
						if (e.originalEvent.shiftKey) {
							target = closestRelevantBlock.previousElementSibling;
							
							// first `<td>` of current `<tr>`
							if (target === null) {
								target = closestRelevantBlock.parentNode.previousElementSibling;
								
								if (target !== null) {
									// set focus to last `<td>`
									target = target.lastElementChild;
								}
							}
						}
						else {
							target = closestRelevantBlock.nextElementSibling;
							
							// last `<td>` of current `<tr>`
							if (target === null) {
								target = closestRelevantBlock.parentNode.nextElementSibling;
								
								// last `<tr>`
								if (target === null) {
									this.table.addRowBelow();
									
									target = closestRelevantBlock.parentNode.nextElementSibling;
								}
								
								// set focus to first `<td>`
								target = target.firstElementChild;
							}
						}
						
						if (target !== null) {
							if (this.utils.isEmpty(target.innerHTML)) {
								// `<td>` is empty
								this.caret.end(target);
							}
							else {
								// select the entire content
								var range = document.createRange();
								range.selectNodeContents(target);
								
								selection.removeAllRanges();
								selection.addRange(range);
							}
						}
						
						e.originalEvent.preventDefault();
						return false;
					}
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
			
			var mpRemoveInvisibleSpace = this.keydown.removeInvisibleSpace;
			this.keydown.removeInvisibleSpace = (function() {
				// Firefox on Android sets the caret to the editor root when backspacing an empty editor,
				// potentially causing the editor itself to be removed.
				if (this.keydown.current !== this.$editor[0]) {
					mpRemoveInvisibleSpace.call(this);
				}
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
			
			// Firefox misbehaves when backspacing/deleting inside custom elements,
			// causing the element to  be split into (at least) two separate blocks.
			// The code below aims to solve this by detect these fragmented elements
			// and merge them again.
			// 
			// See https://bugzilla.mozilla.org/show_bug.cgi?id=1329639
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
									
									var node;
									while (block.childNodes.length) {
										node = block.childNodes[0];
										
										// avoid moving contents that follows a `<br>`
										if (node.nodeName === 'BR') {
											elRemove(node);
											break;
										}
										
										sibling.appendChild(node);
									}
									
									// blocks may be non-empty if they contained a `<br>` somehwere after the original caret position
									if (block.childNodes.length === 0) {
										elRemove(block);
									}
									
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
			
			var getSelectorForCustomElements = (function() {
				return this.opts.blockTags.filter(function(tagName) {
					return tagName.indexOf('-') !== -1;
				}).join(',');
			}).bind(this);
			
			var firefoxKnownCustomElements = [];
			var firefoxDetectSplitCustomElements = (function() {
				elBySelAll(getSelectorForCustomElements(), this.core.editor()[0], function(element) {
					if (element.parentNode === null) {
						// ignore elements that have already been handled
						return;
					}
					
					if (firefoxKnownCustomElements.indexOf(element) === -1) {
						// element did not exist prior to the backspace/delete action
						return;
					}
					
					['nextElementSibling', 'previousElementSibling'].forEach(function(elementSibling) {
						var next, sibling = element[elementSibling];
						while (sibling !== null && sibling.nodeName === element.nodeName && firefoxKnownCustomElements.indexOf(sibling) === -1) {
							// move all child nodes into the "real" element
							if (elementSibling === 'previousElementSibling') {
								for (var i = sibling.childNodes.length - 1; i >= 0; i--) {
									element.insertBefore(sibling.childNodes[i], element.firstChild);
								}
							}
							else {
								while (sibling.childNodes.length > 0) {
									element.appendChild(sibling.childNodes[0]);
								}
							}
							
							// continue with the next sibling
							next = sibling[elementSibling];
							elRemove(sibling);
							sibling = next;
						}
					});
				});
				
				firefoxKnownCustomElements = [];
			}).bind(this);
			
			this.keydown.onBackspaceAndDeleteAfter = (function (e) {
				//noinspection JSValidateTypes
				if (this.detect.isFirefox()) {
					if (this.selection.isCollapsed()) {
						if (e.which === this.keyCode.BACKSPACE) {
							firefoxHandleBackspace(e);
						}
						else if (e.which === this.keyCode.DELETE) {
							firefoxHandleDelete(e);
						}
					}
					else {
						if (e.which === this.keyCode.BACKSPACE || e.which === this.keyCode.DELETE) {
							elBySelAll(getSelectorForCustomElements(), this.core.editor()[0], function(element) {
								firefoxKnownCustomElements.push(element);	
							});
						}
					}
				}
				
				var isInsidePre = false;
				if (e.which === this.keyCode.BACKSPACE && this.selection.isCollapsed() && this.detect.isWebkit()) {
					var block = this.selection.block();
					if (block !== false && block.nodeName === 'PRE') {
						isInsidePre = true;
					}
				}
				
				// remove style tag
				setTimeout($.proxy(function()
				{
					var current;
					
					if (firefoxKnownCustomElements.length > 0) {
						firefoxDetectSplitCustomElements();
					}
					
					// The caret was previously inside a `<pre>`, check if we have backspaced out
					// of the code and are now left inside a `<span>` with a metric ton of styles. 
					if (isInsidePre) {
						var block = this.selection.block();
						if (block === false || block.nodeName !== 'PRE') {
							current = this.selection.current();
							// If the keystroke caused the `<pre>` to vanish, then the caret has moved into the
							// adjacent element, but the `current`'s next sibling is the newly added `<span>`.
							if (current.nodeType === Node.TEXT_NODE && current.nextSibling && current.nextSibling.nodeName === 'SPAN') {
								var sibling = current.nextSibling;
								
								// check for typical styles that are a remains of the `<pre>`'s styles
								if (sibling.style.getPropertyValue('font-family').indexOf('monospace') !== -1 && sibling.style.getPropertyValue('white-space') === 'pre-wrap') {
									// the sibling is a rogue `<span>`, remove it
									while (sibling.childNodes.length) {
										sibling.parentNode.insertBefore(sibling.childNodes[0], sibling);
									}
									elRemove(sibling);
								}
							}
						}
					}
					
					this.code.syncFire = false;
					this.keydown.removeEmptyLists();
					
					var filter = '';
					if (this.opts.keepStyleAttr.length !== 0) {
						filter = ',' + this.opts.keepStyleAttr.join(',');
					}
					
					// WoltLab modification: allow style tag on `<span>`
					var $styleTags = this.core.editor().find('*[style]');
					$styleTags.not('span, img, figure, iframe, #redactor-image-box, #redactor-image-editter, [data-redactor-style-cache], [data-redactor-span]' + filter).removeAttr('style');
					
					this.keydown.formatEmpty(e);
					
					// strip empty <kbd>
					current = this.selection.current();
					if (current.nodeName === 'KBD' && current.innerHTML.length === 0) {
						elRemove(current);
					}
					
					this.code.syncFire = true;
					
				}, this), 1);
			}).bind(this);
		}
	}
};
