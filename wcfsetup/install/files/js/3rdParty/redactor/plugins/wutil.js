if (!RedactorPlugins) var RedactorPlugins = {};

/**
 * Provides utility methods extending $.Redactor
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
RedactorPlugins.wutil = function() {
	"use strict";
	
	var $autosaveLastMessage = '';
	var $autosaveNotice = null;
	var $autosaveDidSave = false;
	var $autosavePaused = false;
	var $autosaveSaveNoticePE = null;
	
	var _editor = null;
	var _range =  null;
	var _textarea = null;
	
	return {
		/**
		 * autosave worker process
		 * @var	WCF.PeriodicalExecuter
		 */
		_autosaveWorker: null,
		
		/**
		 * Initializes the RedactorPlugins.wutil plugin.
		 */
		init: function() {
			_editor = this.$editor[0];
			_textarea = this.$textarea[0];
			
			// convert HTML to BBCode upon submit
			this.$textarea.parents('form').submit($.proxy(this.wutil.submit, this));
			
			WCF.System.Event.addListener('com.woltlab.wcf.redactor', 'getText_' + _textarea.id, (function(data) {
				data.message = this.wutil.getText();
			}).bind(this));
		},
		
		/**
		 * Saves current caret position.
		 * 
		 * @param	boolean		discardSavedIfEmpty
		 */
		saveSelection: function(discardSavedIfEmpty) {
			var selection = window.getSelection();
			
			if (selection.rangeCount) {
				_range = selection.getRangeAt(0);
			}
			else if (discardSavedIfEmpty) {
				_range = null;
			}
		},
		
		/**
		 * Restores saved selection.
		 */
		restoreSelection: function() {
			if (document.activeElement !== _editor) {
				_editor.focus();
			}
			
			if (_range !== null) {
				var selection = window.getSelection();
				selection.removeAllRanges();
				selection.addRange(_range);
				
				_range = null;
			}
		},
		
		/**
		 * Clears the current selection.
		 */
		clearSelection: function() {
			_range = null;
		},
		
		/**
		 * Returns stored selection or null.
		 * 
		 * @return	Range
		 */
		getSelection: function() {
			return _range;
		},
		
		/**
		 * Allows inserting of text contents in Redactor's source area.
		 * 
		 * @param	string		string
		 * @return	boolean
		 */
		insertAtCaret: function(string) {
			if (this.opts.visual) {
				console.debug("insertAtCaret() failed: Editor is in WYSIWYG-mode.");
				return false;
			}
			
			_textarea.focus();
			var $position = this.$textarea.getCaret();
			if ($position == -1) {
				console.debug("insertAtCaret() failed: Source is not input[type=text], input[type=password] or textarea.");
			}
			
			var $content = _textarea.value;
			$content = $content.substr(0, $position) + string + $content.substr($position);
			_textarea.value = $content;
			
			return true;
		},
		
		/**
		 * Inserts content into the editor depending if it is in wysiwyg or plain mode. If 'plainValue' is
		 * null or undefined, the value from 'html' will be taken instead.
		 * 
		 * @param	string		html
		 * @param	string		plainValue
		 */
		insertDynamic: function(html, plainValue) {
			if (this.wutil.inWysiwygMode()) {
				this.insert.html(html, false);
			}
			else {
				if (plainValue === undefined || plainValue === null) {
					plainValue = html;
				}
				
				this.wutil.insertAtCaret(plainValue);
			}
		},
		
		/**
		 * Sets an option value after initialization.
		 * 
		 * @param	string		key
		 * @param	mixed		value
		 */
		setOption: function(key, value) {
			if (key.indexOf('.') !== -1) {
				key = key.split('.', 2);
				this.opts[key[0]][key[1]] = value;
			}
			else {
				this.opts[key] = value;
			}
		},
		
		/**
		 * Reads an option value, returns null if key is unknown.
		 * 
		 * @param	string		key
		 * @return	mixed
		 */
		getOption: function(key) {
			if (key.indexOf('.') !== -1) {
				key = key.split('.', 2);
				if (this.opts[key[0]][key[1]]) {
					return this.opts[key[0]][key[1]];
				}
			}
			else if (this.opts[key]) {
				return this.opts[key];
			}
			
			return null;
		},
		
		/**
		 * Returns true if editor is in source mode.
		 * 
		 * @return	boolean
		 */
		inPlainMode: function() {
			return !this.opts.visual;
		},
		
		/**
		 * Returns true if editor is in WYSIWYG mode.
		 * 
		 * @return	boolean
		 */
		inWysiwygMode: function() {
			return (this.opts.visual);
		},
		
		/**
		 * Replaces all ranges from the current selection with the provided one.
		 * 
		 * @param	DOMRange	range
		 */
		replaceRangesWith: function(range) {
			getSelection().removeAllRanges();
			getSelection().addRange(range);
		},
		
		/**
		 * Returns text using BBCodes.
		 * 
		 * @return	string
		 */
		getText: function() {
			if (this.wutil.inWysiwygMode()) {
				this.code.startSync();
				
				_textarea.value = this.wbbcode.convertFromHtml(_textarea.value).trim();
			}
			
			return _textarea.value.trim();
		},
		
		/**
		 * Returns true if editor is empty.
		 * 
		 * @return	boolean
		 */
		isEmptyEditor: function() {
			if (this.opts.visual) {
				return this.utils.isEmpty(_editor.innerHTML);
			}
			
			return (_textarea.value.trim() === '');
		},
		
		/**
		 * Converts HTML to BBCode upon submit.
		 */
		submit: function() {
			if (this.wutil.inWysiwygMode()) {
				this.code.startSync();
				
				_textarea = this.wbbcode.convertFromHtml(_textarea.value).trim();
			}
			
			this.wautosave.purge();
		},
		
		/**
		 * Resets the editor's contents.
		 */
		reset: function() {
			if (this.opts.visual) {
				_editor.innerHTML = '';
				this.wutil.saveSelection();
			}
			
			_textarea.value = '';
			
			WCF.System.Event.fireEvent('com.woltlab.wcf.redactor', 'reset', { wysiwygContainerID: _textarea.id });
		},
		
		/** @deprecated	2.2 - please use `wautosave.enable()` instead */
		autosaveEnable: function(key) { this.wautosave.enable(key); },
		
		/** @deprecated	2.2 - please use `wautosave.save()` instead */
		saveTextToStorage: function(force) { this.wautosave.save(force); },
		
		/** @deprecated	2.2 - please use `wautosave.disable()` instead */
		autosaveDisable: function() { return this.wautosave.disable(); },
		
		/** @deprecated	2.2 - please use `wautosave.purge()` instead */
		autosavePurge: function() { this.wautosave.purge(); },
		
		/** @deprecated	2.2 - please use `wautosave.restore()` instead */
		autosaveRestore: function() { return this.wautosave.restore(); },
		
		/** @deprecated	2.2 - please use `wautosave.showNotice()` instead */
		autosaveShowNotice: function(type, data) { this.wautosave.showNotice(type, data); },
		
		/** @deprecated	2.2 - please use `wautosave.purgeOutdated()` instead */
		autosavePurgeOutdated: function() { this.wautosave.purgeOutdated(); },
		
		/** @deprecated	2.2 - please use `wautosave.pause()` instead */
		autosavePause: function() { this.wautosave.pause(); },
		
		/** @deprecated	2.2 - please use `wautosave.resume()` instead */
		autosaveResume: function() { this.wautosave.resume(); },
		
		/**
		 * Replaces one button with a new one.
		 * 
		 * @param	string		target
		 * @param	string		key
		 * @param	string		title
		 * @param	object		callback
		 * @param	object		dropdown
		 * @return	jQuery
		 */
		buttonReplace: function(target, key, title, callback, dropdown) {
			var $target = this.buttonGet(target);
			
			var $button = this.buttonAddAfter(target, key, title, callback, dropdown);
			if ($target.parent().hasClass('separator')) {
				$button.parent().addClass('separator');
			}
			
			$target.parent().remove();
			
			return $button;
		},
		
		/**
		 * Removes the unicode zero-width space (0x200B).
		 * 
		 * @param	string		string
		 * @return	string
		 */
		removeZeroWidthSpace: function(string) {
			return string.replace(/\u200b/g, '');
		},
		
		/**
		 * Returns source textarea object.
		 * 
		 * @deprecated	2.2 - please use `core.getTextarea()` instead
		 * @return	jQuery
		 */
		getSource: function() {
			return this.$textarea;
		},
		
		/**
		 * Returns editor instance name.
		 * 
		 * @return	string
		 */
		getName: function() {
			return _textarea.id;
		},
		
		/**
		 * Sets the selection after the last direct children of the editor.
		 */
		selectionEndOfEditor: function() {
			var lastChild = _editor.lastElementChild;
			if (lastChild === null || lastChild.nodeName === 'BLOCKQUOTE' || (lastChild.nodeName === 'DIV' && lastChild.classList.contains('codeBox')) || lastChild.nodeName === 'KBD') {
				var element = this.utils.createSpaceElement();
				_editor.appendChild(element);
				
				this.caret.setEnd(element);
				this.wutil.saveSelection();
			}
			else {
				this.focus.setEnd();
			}
		},
		
		/**
		 * Inserting block-level elements into other blocks or inline elements can mess up the entire DOM,
		 * this method tries to find the best nearby insert location.
		 */
		adjustSelectionForBlockElement: function() {
			if (document.activeElement !== _editor) {
				this.wutil.restoreSelection();
			}
			
			if (!window.getSelection().rangeCount) {
				return;
			}
			
			var range = window.getSelection().getRangeAt(0);
			if (range.collapsed) {
				var element = range.startContainer;
				if (element.nodeType === Node.TEXT_NODE && element.parentNode && element.parentNode.parentNode === _editor) {
					// caret position is fine
					return;
				}
				else {
					// walk tree up until we find a direct children of the editor and place the caret afterwards
					while (element && element !== _editor) {
						element = element.parentNode;
					}
					
					if (element.parentNode === _editor) {
						this.caret.setAfter(element);
					}
					else {
						// work-around if selection never has been within the editor before
						this.wutil.selectionEndOfEditor();
					}
				}
			}
		},
		
		/**
		 * Returns true if current selection is just a caret or false if selection spans content.
		 * 
		 * @return	boolean
		 */
		isCaret: function() {
			this.selection.get();
			
			return this.range.collapsed;
		},
		
		/**
		 * Returns true if current selection is just a caret and it is the last possible offset
		 * within the given element.
		 * 
		 * @param	Element		element
		 * @return	boolean
		 */
		isEndOfElement: function(element) {
			// prefer our range because it is more reliable
			var $range = this.selection.implicitRange;
			if ($range === null) {
				this.selection.get();
				$range = this.range;
			}
			
			// range is not a plain caret
			if (!this.wutil.isCaret()) {
				return false;
			}
			
			if ($range.endContainer.nodeType === Node.TEXT_NODE) {
				// caret is not at the end
				if ($range.endOffset < $range.endContainer.length) {
					return false;
				}
			}
			
			// range is not within the provided element
			if (!this.wutil.isNodeWithin($range.endContainer, element)) {
				return false;
			}
			
			var $current = $range.endContainer;
			while ($current !== element) {
				// end of range is not the last element
				if ($current.nextSibling) {
					return false;
				}
				
				$current = $current.parentNode;
			}
			
			return true;
		},
		
		/**
		 * Returns true if the provided node is a direct or indirect child of the target element. This
		 * method works similar to jQuery's $.contains() but works recursively.
		 * 
		 * @param	Element		node
		 * @param	Element		element
		 * @return	boolean
		 */
		isNodeWithin: function(node, element) {
			while (node && node !== _editor) {
				if (node === element) {
					return true;
				}
				
				node = node.parentNode;
			}
			
			return false;
		},
		
		/**
		 * Returns true if the given node equals the provided tagName or contains it.
		 * 
		 * @param	Element		node
		 * @param	string		tagName
		 * @return	boolean
		 */
		containsTag: function(node, tagName) {
			if (node.nodeType === Node.ELEMENT_NODE) {
				if (node.nodeName === tagName) {
					return true;
				}
			}
			else if (node.nodeType === Node.DOCUMENT_FRAGMENT_NODE) {
				for (var i = 0, length = node.childElementCount; i < length; i++) {
					if (this.wutil.containsTag(node.childNodes[i], tagName)) {
						return true;
					}
				}
			}
			
			return false;
		},
		
		/**
		 * Replaces the current content with the provided value.
		 * 
		 * @param	string		value
		 */
		replaceText: function(value) {
			var $document = $(document);
			var $offsetTop = $document.scrollTop();
			var $wasInWysiwygMode = false;
			
			if (this.wutil.inWysiwygMode()) {
				this.code.toggle();
				$wasInWysiwygMode = true;
			}
			
			_textarea.value = value;
			
			if ($wasInWysiwygMode) {
				this.code.toggle();
				
				// restore scrolling since editor receives the focus
				$document.scrollTop($offsetTop);
			}
			
			// trigger resize event to rebuild message tab menu
			$document.trigger('resize');
		},
		
		/**
		 * Sets the caret before the given element.
		 * 
		 * @param	Element		element
		 */
		setCaretBefore: function(element) {
			this.caret.setBefore(element);
		},
		
		/**
		 * Sets the caret after the given element.
		 * 
		 * @param	Element		element
		 */
		setCaretAfter: function(element) {
			this.caret.setAfter(element);
		},
		
		/**
		 * Sets the caret at target position.
		 * 
		 * @deprecated	2.2 - please use `wutil.setCaret(Before|After)()` instead
		 * @param	Element		element
		 * @param	boolean		setBefore
		 */
		_setCaret: function(element, setBefore) {
			this.caret[(setBefore ? 'setBefore' : 'setAfter')](element);
		},
		
		/**
		 * Fixes the DOM after pasting:
		 *  - move all non-element children of the editor into a paragraph
		 *  - pasting lists/list-items in lists can yield empty <li></li>
		 */
		fixDOM: function() {
			var elements = _editor.querySelectorAll('li:empty'), parent;
			for (var i = 0, length = elements.length; i < length; i++) {
				parent = elements[i].parentNode;
				if (parent.childElementCount > 1) {
					parent.removeChild(elements[i]);
				}
			}
			
			// remove input elements
			elements = _editor.getElementsByTagName('INPUT');
			while (elements.length) elements[0].parentNode.removeChild(elements[0]);
		}
	};
};
