$.Redactor.prototype.WoltLabCaret = function() {
	"use strict";
	
	var _iOS = false;
	var _isSafari = false;
	var _touchstartTarget;
	
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
			
			var mpStart = this.caret.start;
			this.caret.start = (function (node) {
				if (_isSafari) {
					var sel, range;
					node = this.caret.prepare(node);

					if (!node) {
						return;
					}
					
					// iOS Safari offsets the caret by a half if the only content is an invisible space.
					if (node.nodeName === 'P' && node.innerHTML === '\u200b') {
						node.innerHTML = '<br>';
					}
				}
				
				mpStart.call(this, node);
			}).bind(this);
			
			var editor = this.core.editor()[0];
			require(['Environment'], (function (Environment) {
				_iOS = (Environment.platform() === 'ios');
				_isSafari = (Environment.browser() === 'safari');
				
				if (_isSafari) {
					editor.classList.add('jsSafariMarginClickTarget');
				}
				
				var handleEditorClick = this.WoltLabCaret._handleEditorClick.bind(this);
				var handleEditorMouseUp = this.WoltLabCaret._handleEditorMouseUp.bind(this);
				if (_isSafari && _iOS) {
					editor.addEventListener('touchstart', function(e) {
						_touchstartTarget = e.target;
					}, { passive: true });
					
					editor.addEventListener('touchend', (function (event) {
						handleEditorClick(event);
						handleEditorMouseUp(event);
					}).bind(this));
				}
				else {
					editor.addEventListener('click', (function(event) {
						this.WoltLabCaret._detectTripleClick(event);
						
						handleEditorClick(event);
					}).bind(this));
					editor.addEventListener('mouseup', handleEditorMouseUp);
				}
				
			}).bind(this));
			
			var mpEnd = this.caret.end;
			this.caret.end = (function (node) {
				node = this.caret.prepare(node);
				
				// handle trailing lists
				if (node.nodeName === 'OL' || node.nodeName === 'UL') {
					node = node.lastElementChild;
					
					if (node === null) node = node.parentNode;
				}
				
				var useCustomRange = false;
				if (node.nodeType === Node.ELEMENT_NODE && node.lastChild && node.lastChild.nodeName === 'P') {
					useCustomRange = true;
				}
				else if (_iOS) {
					var editor = this.core.editor()[0];
					if (node.parentNode === editor && editor.innerHTML === '<p><br></p>') {
						useCustomRange = true;
					}
				}
				else if (node.nodeName === 'P' && node.childNodes.length === 0) {
					node.innerHTML = '\u200B';
					useCustomRange = true;
				}
				
				if (useCustomRange) {
					var selection = window.getSelection();
					var range = document.createRange();
					range.selectNodeContents(node.lastChild);
					range.collapse(false);
					
					selection.removeAllRanges();
					selection.addRange(range);
					
					return;
				}
				
				// calling `caret.end()` on `<p><br></p>` will cause a new
				// blank line to be inserted after the node instead
				if (node.nodeName === 'P' && node.childNodes.length === 1 && node.childNodes[0].nodeName === 'BR') {
					return this.caret.before(node.childNodes[0]);
				}
				
				return mpEnd.call(this, node);
			}).bind(this);
			
			var mpSelectionNodes = this.selection.nodes;
			this.selection.nodes = (function (tag) {
				var returnValues = mpSelectionNodes.call(this, tag);
				if (returnValues.length === 1 && returnValues[0] === this.$editor[0]) {
					var range = this.selection.range(this.selection.get());
					if (range.startContainer === range.endContainer) {
						return [range.startContainer];
					}
				}
				
				return returnValues;
			}).bind(this);
			
			this.WoltLabCaret._initInternalRange();
			
			var mpSaveInstant = this.selection.saveInstant;
			this.selection.saveInstant = (function() {
				var saved = mpSaveInstant.call(this);
				
				if (saved) {
					saved.isAtNodeStart = false;
					
					var selection = window.getSelection();
					if (selection.rangeCount && !selection.isCollapsed) {
						var range = selection.getRangeAt(0);
						if (range.startContainer.nodeType === Node.TEXT_NODE && range.startOffset === 0) {
							saved.isAtNodeStart = true;
						}
					}
				}
				
				return saved;
			}).bind(this);
			
			var mpRestoreInstant = this.selection.restoreInstant;
			this.selection.restoreInstant = (function(saved) {
				if (typeof saved === 'undefined' && !this.saved) {
					return;
				}
				
				var localSaved = (typeof saved !== 'undefined') ? saved : this.saved;
				
				mpRestoreInstant.call(this, saved);
				
				var selection = window.getSelection();
				if (!selection.rangeCount) return;
				
				if (localSaved.isAtNodeStart === true) {
					if (!selection.isCollapsed) {
						var range = selection.getRangeAt(0);
						var start = range.startContainer;
						
						if (localSaved.node === start) {
							// selection is valid
							return;
						}
						
						// find the parent <p>
						while (start !== null && start.nodeName !== 'P') start = start.parentNode;
						
						if (start !== null) {
							// check if the next element sibling is an empty <p>
							start = start.nextElementSibling;
							if (start !== null && start.nodeName === 'P' && start.textContent.replace(/\u200B/g, '').length === 0) {
								start = start.nextElementSibling;
								
								var parent = localSaved.node;
								while (parent !== null && parent !== start) parent = parent.parentNode;
								
								if (parent === start) {
									// force selection to start with the original start node
									range = range.cloneRange();
									range.setStart(localSaved.node, 0);
									
									selection.removeAllRanges();
									selection.addRange(range);
								}
							}
						}
					}
				}
				else if (selection.isCollapsed) {
					var anchorNode = selection.anchorNode;
					var editor = this.core.editor()[0];
					
					// Restoring a selection may fail if the node does was removed from the DOM,
					// causing the caret to be inside a text node with the editor being the
					// direct parent. We can safely move the caret inside the adjacent container,
					// using `caret.start()`.
					if (anchorNode.nodeType === Node.TEXT_NODE && anchorNode.parentNode === editor && selection.anchorOffset === anchorNode.textContent.length) {
						var p = anchorNode.nextElementSibling;
						if (p && p.nodeName === 'P') {
							this.caret.start(p);
						}
					}
				}
			}).bind(this);
			
			this.selection.nodes = (function(tag) {
				var filter = (typeof tag === 'undefined') ? [] : (($.isArray(tag)) ? tag : [tag]);
				
				var sel = this.selection.get();
				var range = this.selection.range(sel);
				var nodes = [];
				var resultNodes = [];
				
				if (this.utils.isCollapsed()) {
					nodes = [this.selection.current()];
				}
				else {
					var node = range.startContainer;
					var endNode = range.endContainer;
					
					// single node
					if (node === endNode) {
						return [node];
					}
					
					// iterate
					var commonAncestorContainer = range.commonAncestorContainer;
					while (node && node !== endNode) {
						// WoltLab modification: prevent `node` from breaking out of the `commonAncestorContainer`
						//nodes.push(node = this.selection.nextNode(node));
						nodes.push(node = this.selection.nextNode(node, commonAncestorContainer));
					}
					
					// partially selected nodes
					node = range.startContainer;
					while (node && node !== commonAncestorContainer) {
						nodes.unshift(node);
						node = node.parentNode;
					}
				}
				
				// remove service nodes
				$.each(nodes, function (i, s) {
					if (s) {
						var tagName = (s.nodeType !== 1) ? false : s.tagName.toLowerCase();
						
						if ($(s).hasClass('redactor-script-tag') || $(s).hasClass('redactor-selection-marker')) {
							return;
						}
						else if (tagName && filter.length !== 0 && $.inArray(tagName, filter) === -1) {
							return;
						}
						else {
							resultNodes.push(s);
						}
					}
				});
				
				return (resultNodes.length === 0) ? [] : resultNodes;
			}).bind(this);
			
			// WoltLab modification: Added the `container` parameter
			this.selection.nextNode = function(node, container) {
				if (node.hasChildNodes()) {
					return node.firstChild;
				}
				else {
					while (node && !node.nextSibling) {
						node = node.parentNode;
						
						// WoltLab modification: do not allow the `node` to escape `container`
						if (container && node === container) {
							return null;
						}
					}
					
					if (!node) {
						return null;
					}
					
					return node.nextSibling;
				}
			};
		},
		
		paragraphAfterBlock: function (block) {
			var sibling = block.nextElementSibling;
			if (sibling && sibling.nodeName !== 'P') {
				sibling = elCreate('p');
				if (this.opts.emptyHtml === '<p><br></p>') {
					sibling.innerHTML = '<br>';
				}
				else {
					sibling.textContent = '\u200B';
				}
				block.parentNode.insertBefore(sibling, block.nextSibling);
			}
			
			this.caret.after(block);
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
			
			this.WoltLabCaret.forceSelectionSave = saveRange;
			
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
				
				// the scroll position is discarded when focusing the editor
				var scrollLeft = editor.scrollLeft;
				var scrollTop = editor.scrollTop;
				editor.focus();
				editor.scrollLeft = scrollLeft;
				editor.scrollTop = scrollTop;
				
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
				if (internalRange && elBySel('.redactor-selection-marker', this.$editor[0]) === null) {
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
					// check if caret is still inside the editor
					var selection = window.getSelection();
					if (selection.rangeCount && this.utils.isRedactorParent(selection.anchorNode) !== false) {
						editor.focus();
					}
					else {
						restoreRange();
					}
				}
				
				mpSet.call(this, type);
				
				saveRange();
			}).bind(this);
			
			var mpHtml = this.insert.html;
			this.insert.html = (function (html, data) {
				var hasMarker = elBySel('.redactor-selection-marker', this.$editor[0]);
				
				mpHtml.call(this, html, data);
				
				if (hasMarker || elBySel('.redactor-selection-marker', this.$editor[0]) === null) {
					saveRange();
				}
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

		/**
		 * Many browsers will mark the entire line of text on triple click. However, the selection can
		 * sometimes spill into an adjacent block, for example, highlighting an entire line will also
		 * select the empty (!) start of the next line. For tables, this could cause the selection to
		 * include the - again empty - start of the adjacent cell.
		 * 
		 * @param {MouseEvent} event
		 * @protected
		 */
		_detectTripleClick: function(event) {
			// Anything over 3 clicks behaves as a triple click.
			if (event.detail < 3) {
				return;
			}
			
			var selection = window.getSelection();
			if (!selection.isCollapsed) {
				var range = selection.getRangeAt(0);
				if (range.commonAncestorContainer.nodeName === 'TR') {
					// The `<tr>` most likely indicates a selection that spans two cells, reduce the
					// selection to only include the first cell.
					var td = elClosest(range.startContainer, 'td');
					
					range = document.createRange();
					range.selectNodeContents(td);
					
					selection.removeAllRanges();
					selection.addRange(range);
				}
			}
		},
		
		_handleEditorClick: function (event) {
			var clientY = event.clientY;
			if (!this.selection.get().isCollapsed) {
				if (_isSafari && _iOS && _touchstartTarget === event.target && this.utils.isBlockTag(_touchstartTarget.nodeName)) {
					// Treat this as a collapsed selection instead, because the iOS Safari
					// breaks event delegation and refuses to trigger click-style events
					// for non-link/non-input elements. Thanks Apple.
					clientY = event.changedTouches[0].clientY;
				}
				else {
					// ignore text selection
					return;
				}
			}
			
			var block = this.selection.block();
			if (block === false) {
				// check if the caret is now in a <p> before a <table>
				// which also happens to be the last element
				if (this.selection.current() === this.$editor[0]) {
					var node = this.$editor[0].childNodes[this.selection.get().anchorOffset];
					if (node.nodeType === Node.ELEMENT_NODE && node.nodeName === 'TABLE') {
						block = node;
					}
				}
				
				if (block === false) {
					return;
				}
			}
			
			// Safari moves the caret before triggering the `click` event, causing the
			// selection to appear at the first possible text node, even if it is nowhere
			// near the click position.
			var isSafariMarginHit = false;
			if (_isSafari && this.utils.isBlockTag(event.target.nodeName)) {
				// check if the click occured inside the margin at the block's bottom
				if (clientY > event.target.getBoundingClientRect().bottom) {
					block = event.target;
					isSafariMarginHit = true;
				}
			}
			
			// get block element that received the click
			var targetBlock = event.target;
			while (targetBlock && !this.utils.isBlockTag(targetBlock.nodeName)) {
				targetBlock = targetBlock.parentNode;
			}
			
			if (!targetBlock || (!isSafariMarginHit && targetBlock === block)) {
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
			else if ($(block).closest('ol, ul', this.$editor[0]).length) {
				return;
			}
			
			// handle nested blocks
			var insertBefore, rect;
			var parent = block;
			while (parent) {
				rect = parent.getBoundingClientRect();
				
				if (clientY < rect.top) {
					insertBefore = true;
					block = parent;
				}
				else if (clientY > rect.bottom) {
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
			
			// click occured inside the boundaries of the block
			if (insertBefore === undefined) {
				return;
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
		
		_handleEditorMouseUp: function (event) {
			var anchorNode, sibling;
			
			var selection = window.getSelection();
			if (!selection.isCollapsed) {
				if (_isSafari && _iOS && _touchstartTarget === event.target && this.utils.isBlockTag(_touchstartTarget.nodeName)) {
					// Treat this as a collapsed selection instead, because the iOS Safari
					// breaks event delegation and refuses to trigger click-style events
					// for non-link/non-input elements. Thanks Apple.
				}
				else {
					return;
				}
			}
			
			// click occured inside the editor padding
			if (event.target === this.$editor[0]) {
				anchorNode = selection.anchorNode;
				if (anchorNode.nodeType === Node.TEXT_NODE) anchorNode = anchorNode.parentNode;
				
				// click occured before a `<kbd>` element
				if (anchorNode.nodeName === 'KBD') {
					sibling = anchorNode.previousSibling;
					if (sibling === null || sibling.textContent !== '\u200b') {
						sibling = document.createTextNode('\u200b');
						anchorNode.parentNode.insertBefore(sibling, anchorNode);
					}
					
					this.caret.before(sibling);
				}
			}
			else if (event.target.nodeName === 'KBD') {
				var kbd = event.target;
				
				// check if the user clicked on a `<kbd>` element, but the browser placed the caret to the left
				anchorNode = selection.anchorNode;
				if (anchorNode.nodeType === Node.TEXT_NODE) {
					// check if the first next sibling is the `<kbd>` while skipping all empty text nodes
					sibling = anchorNode;
					while (sibling = sibling.nextSibling) {
						if (sibling.nodeType !== Node.TEXT_NODE || (sibling.textContent !== '' && sibling.textContent !== '\u200b')) {
							break;
						}
					}
					
					if (sibling === kbd) {
						if (kbd.childNodes.length === 0 || kbd.childNodes[0].textContent !== '\u200b') {
							var textNode = document.createTextNode('\u200b');
							kbd.insertBefore(textNode, kbd.firstChild);
						}
						
						var range = document.createRange();
						range.setStartAfter(kbd.childNodes[0]);
						range.setEndAfter(kbd.childNodes[0]);
						
						selection.removeAllRanges();
						selection.addRange(range);
					}
				}
			}
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
