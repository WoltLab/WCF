if (!RedactorPlugins) var RedactorPlugins = {};

/**
 * Provides utility methods extending $.Redactor
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
RedactorPlugins.wutil = function() {
	"use strict";
	
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
			// convert HTML to BBCode upon submit
			this.$textarea.parents('form').submit($.proxy(this.wutil.submit, this));
			
			if (this.wutil.getOption('woltlab.autosave').active) {
				this.wutil.autosaveEnable();
				
				if (this.wutil.getOption('woltlab.autosave').saveOnInit || this.$textarea.data('saveOnInit')) {
					this.wutil.setOption('woltlab.autosaveOnce', true);
				}
				else {
					this.wutil.autosaveRestore();
				}
			}
			
			// prevent Redactor's own autosave
			this.wutil.setOption('autosave', false);
			
			// disable autosave on destroy
			var $mpDestroy = this.core.destroy;
			this.core.destroy = (function() {
				this.wutil.autosaveDisable();
				
				$mpDestroy.call(this);
			}).bind(this);
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
			
			this.$textarea.focus();
			var $position = this.$textarea.getCaret();
			if ($position == -1) {
				console.debug("insertAtCaret() failed: Source is not input[type=text], input[type=password] or textarea.");
			}
			
			var $content = this.$textarea.val();
			$content = $content.substr(0, $position) + string + $content.substr($position);
			this.$textarea.val($content);
			
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
				this.$textarea.val($.trim(this.wbbcode.convertFromHtml(this.$textarea.val())));
			}
			
			return $.trim(this.$textarea.val());
		},
		
		/**
		 * Returns true if editor is empty.
		 * 
		 * @return	boolean
		 */
		isEmptyEditor: function() {
			if (this.opts.visual) {
				return this.utils.isEmpty(this.$editor.html());
			}
			
			return (!$.trim(this.$textarea.val()));
		},
		
		/**
		 * Converts HTML to BBCode upon submit.
		 */
		submit: function() {
			if (this.wutil.inWysiwygMode()) {
				this.code.startSync();
				this.$textarea.val($.trim(this.wbbcode.convertFromHtml(this.$textarea.val())));
			}
			
			this.wutil.autosavePurge();
		},
		
		/**
		 * Resets the editor's contents.
		 */
		reset: function() {
			if (this.opts.visual) {
				this.$editor.html('<p>' + this.opts.invisibleSpace + '</p>');
			}
			
			this.$textarea.val('');
			
			WCF.System.Event.fireEvent('com.woltlab.wcf.redactor', 'reset', { wysiwygContainerID: this.$textarea.wcfIdentify() });
		},
		
		/**
		 * Enables automatic saving every minute.
		 * 
		 * @param	string		key
		 */
		autosaveEnable: function(key) {
			if (!this.wutil.getOption('woltlab.autosave').active) {
				this.wutil.setOption('woltlab.autosave', {
					active: true,
					key: key
				});
			}
			
			if (this.wutil._autosaveWorker === null) {
				this.wutil._autosaveWorker = new WCF.PeriodicalExecuter($.proxy(this.wutil._saveTextToStorage, this), 60 * 1000);
			}
			
			return true;
		},
		
		/**
		 * Saves current editor text to local browser storage.
		 */
		_saveTextToStorage: function() {
			localStorage.setItem(this.wutil.getOption('woltlab.autosave').key, this.wutil.getText());
		},
		
		/**
		 * Disables automatic saving.
		 */
		autosaveDisable: function() {
			if (!this.wutil.getOption('woltlab.autosave').active) {
				return false;
			}
			
			this.wutil._autosaveWorker.stop();
			this.wutil._autosaveWorker = null;
			
			this.wutil.setOption('woltlab.autosave', {
				active: false,
				key: ''
			});
			
			return true;
		},
		
		/**
		 * Attempts to purge saved text.
		 * 
		 * @param	string		key
		 */
		autosavePurge: function() {
			localStorage.removeItem(this.wutil.getOption('woltlab.autosave').key);
		},
		
		/**
		 * Attempts to restore a saved text.
		 */
		autosaveRestore: function() {
			var $options = this.wutil.getOption('woltlab.autosave');
			var $text = localStorage.getItem($options.key);
			if ($text !== null) {
				if (this.wutil.inWysiwygMode()) {
					this.wutil.setOption('woltlab.originalValue', $text);
				}
				else {
					this.$textarea.val($text);
				}
				
				return true;
			}
			
			return false;
		},
		
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
			var $string = '';
			
			for (var $i = 0, $length = string.length; $i < $length; $i++) {
				var $byte = string.charCodeAt($i).toString(16);
				if ($byte != '200b') {
					$string += string[$i];
				}
			}
			
			return $string;
		},
		
		/**
		 * Returns source textarea object.
		 * 
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
			return this.$textarea.wcfIdentify();
		},
		
		/**
		 * Sets the selection after the last direct children of the editor.
		 */
		selectionEndOfEditor: function() {
			var $lastChild = this.$editor.children(':last')[0];
			if ($lastChild.tagName === 'P') {
				// sometimes the last <p> is just empty, causing the method to fail
				if ($lastChild.innerHTML === '') {
					$lastChild = $($lastChild).replaceWith($(this.opts.emptyHtml));
					this.caret.setEnd($lastChild[0]);
				}
			}
			else {
				this.wutil.setCaretAfter($lastChild);
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
			this.selection.get();
			
			// range is not a plain caret
			if (!this.wutil.isCaret()) {
				return false;
			}
			
			if (this.range.endContainer.nodeType === Element.TEXT_NODE) {
				// caret is not at the end
				if (this.range.endOffset < this.range.endContainer.length) {
					return false;
				}
			}
			
			// range is not within the provided element
			if (!this.wutil.isNodeWithin(this.range.endContainer, element)) {
				return false;
			}
			
			var $current = this.range.endContainer;
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
			var $node = $(node);
			while ($node[0] !== this.$editor[0]) {
				if ($node[0] === element) {
					return true;
				}
				
				$node = $node.parent();
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
			switch (node.nodeType) {
				case Element.ELEMENT_NODE:
					if (node.tagName === tagName) {
						return true;
					}
					
				// fall through
				case Element.DOCUMENT_FRAGMENT_NODE:
					for (var $i = 0; $i < node.childNodes.length; $i++) {
						if (this.wutil.containsTag(node.childNodes[$i], tagName)) {
							return true;
						}
					}
					
					return false;
				break;
				
				default:
					return false;
				break;
			}
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
			
			this.$textarea.val(value);
			
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
			this.wutil._setCaret(element, true);
		},
		
		/**
		 * Sets the caret after the given element.
		 * 
		 * @param	Element		element
		 */
		setCaretAfter: function(element) {
			this.wutil._setCaret(element, false);
		},
		
		/**
		 * Sets the caret at target position.
		 * 
		 * @param	Element		element
		 * @param	boolean		setBefore
		 */
		_setCaret: function(element, setBefore) {
			var $node = $(this.opts.emptyHtml);
			$node[(setBefore ? 'insertBefore' : 'insertAfter')](element);
			this.caret.setStart($node[0]);
		}
	};
};
