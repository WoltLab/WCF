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
	
	return {
		/**
		 * autosave worker process
		 * @var	WCF.PeriodicalExecuter
		 */
		_autosaveWorker: null,
		
		/**
		 * saved selection range
		 * @var	range
		 */
		_range: null,
		
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
		 * Saves current caret position.
		 * 
		 * @param	boolean		discardSavedIfEmpty
		 */
		saveSelection: function(discardSavedIfEmpty) {
			var $selection = getSelection();
			
			if ($selection.rangeCount) {
				this.wutil._range = $selection.getRangeAt(0);
			}
			else if (discardSavedIfEmpty) {
				this.wutil._range = null;
			}
		},
		
		/**
		 * Restores saved selection.
		 */
		restoreSelection: function() {
			if (document.activeElement !== this.$editor[0]) {
				this.$editor.focus();
			}
			
			if (this.wutil._range !== null) {
				var $selection = window.getSelection();
				$selection.removeAllRanges();
				$selection.addRange(this.wutil._range);
				
				this.wutil._range = null;
			}
		},
		
		/**
		 * Clears the current selection.
		 */
		clearSelection: function() {
			this.wutil._range = null;
		},
		
		/**
		 * Returns stored selection or null.
		 * 
		 * @return	Range
		 */
		getSelection: function() {
			return this.wutil._range;
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
				var $html = this.$textarea.val();
				
				this.$textarea.val($.trim(this.wbbcode.convertFromHtml($html)));
			}
			
			var $text = $.trim(this.$textarea.val());
			
			$text = this.wutil._removeSuperfluousNewlines($text);
			
			return $text;
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
				
				var $text = $.trim(this.wbbcode.convertFromHtml(this.$textarea.val()));
				
				$text = this.wutil._removeSuperfluousNewlines($text);
				
				this.$textarea.val($text);
			}
			
			this.wutil.autosavePurge();
		},
		
		/**
		 * Removes newlines after certain elements which have been inserted for
		 * readability but do not represent an actual newline.
		 * 
		 * @param	string		text
		 * @return	string
		 */
		_removeSuperfluousNewlines: function(text) {
			text = text.replace(/(\[\/(?:align|code|quote)\])\n/g, '$1');
			
			var $data = { text: text };
			WCF.System.Event.fireEvent('com.woltlab.wcf.redactor', 'wutil_removeSuperfluousNewlines', $data);
			
			return $data.text;
		},
		
		/**
		 * Adds newlines after certain elements, this is actually the reverse of
		 * _removeSuperfluousNewlines() which removes them prior to submitting.
		 * 
		 * @param	string
		 * @return	string
		 */
		addNewlines: function(text) {
			text = text.replace(/(\[\/(?:align|code|quote)\])/g, '$1\n');
			
			var $data = { text: text };
			WCF.System.Event.fireEvent('com.woltlab.wcf.redactor', 'wutil_addNewlines', $data);
			
			return $data.text;
		},
		
		/**
		 * Resets the editor's contents.
		 */
		reset: function() {
			if (this.opts.visual) {
				this.$editor.html('<p>' + this.opts.invisibleSpace + '</p>');
				this.wutil.saveSelection();
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
				this.wutil.autosavePurgeOutdated();
				
				this.wutil._autosaveWorker = new WCF.PeriodicalExecuter((function(pe) {
					this.wutil.saveTextToStorage(false);
				}).bind(this), 15 * 1000);
			}
			
			return true;
		},
		
		/**
		 * Saves current editor text to local browser storage.
		 * 
		 * @param	boolean		force
		 */
		saveTextToStorage: function(force) {
			var $content = this.wutil.getText();
			if ($autosaveLastMessage == $content && !force) {
				return;
			}
			
			try {
				localStorage.setItem(this.wutil.getOption('woltlab.autosave').key, JSON.stringify({
					content: $content,
					timestamp: Date.now()
				}));
				$autosaveLastMessage = $content;
				$autosaveDidSave = true;
				
				if ($autosaveSaveNoticePE === null) {
					$autosaveSaveNoticePE = new WCF.PeriodicalExecuter((function(pe) {
						if ($autosavePaused === true) {
							return;
						}
						
						if ($autosaveDidSave === false) {
							pe.stop();
							$autosaveSaveNoticePE = null;
							
							return;
						}
						
						this.wutil.autosaveShowNotice('saved');
						$autosaveDidSave = false;
					}).bind(this), 120 * 1000);
				}
			}
			catch (e) {
				console.debug("[wutil.saveTextToStorage] Unable to access local storage: " + e.message);
			}
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
			try {
				localStorage.removeItem(this.wutil.getOption('woltlab.autosave').key);
			}
			catch (e) {
				console.debug("[wutil.autosavePurge] Unable to access local storage: " + e.message);
			}
		},
		
		/**
		 * Attempts to restore a saved text.
		 * 
		 * @return	boolean
		 */
		autosaveRestore: function() {
			var $options = this.wutil.getOption('woltlab.autosave');
			var $text = null;
			
			try {
				$text = localStorage.getItem($options.key);
			}
			catch (e) {
				console.debug("[wutil.autosaveRestore] Unable to access local storage: " + e.message);
			}
			
			try {
				$text = ($text === null) ? null : JSON.parse($text);
			}
			catch (e) {
				$text = null;
			}
			
			if ($text === null || !$text.content) {
				return false;
			}
			
			if ($options.lastEditTime && ($options.lastEditTime * 1000) > $text.timestamp) {
				// stored message is older than last edit time, consider it tainted and discard
				this.wutil.autosavePurge();
				
				return false;
			}
			
			if ($options.prompt) {
				this.wutil.autosaveShowNotice('prompt', $text);
				
				return false;
			}
			
			if (this.wutil.inWysiwygMode()) {
				this.wutil.setOption('woltlab.originalValue', $text.content);
			}
			else {
				this.$textarea.val($text.content);
			}
			
			this.wutil.autosaveShowNotice('restored', { timestamp: $text.timestamp });
			
			return true;
		},
		
		/**
		 * Displays a notice regarding the autosave feature.
		 * 
		 * @param	string		type
		 * @param	object		data
		 */
		autosaveShowNotice: function(type, data) {
			if ($autosaveNotice === null) {
				$autosaveNotice = $('<div class="redactorAutosaveNotice"><span class="redactorAutosaveMessage" /></div>');
				$autosaveNotice.appendTo(this.$box);
				
				var $resetNotice = (function(event) {
					if (event !== null && event.originalEvent.propertyName !== 'opacity') {
						return;
					}
					
					if ($autosaveNotice.hasClass('open') && event !== null) {
						if ($autosaveNotice.data('callbackOpen')) {
							$autosaveNotice.data('callbackOpen')();
						}
					}
					else {
						if ($autosaveNotice.data('callbackClose')) {
							$autosaveNotice.data('callbackClose')();
						}
						
						$autosaveNotice.removeData('callbackClose');
						$autosaveNotice.removeData('callbackOpen');
						
						$autosaveNotice.removeClass('redactorAutosaveNoticeIcons');
						$autosaveNotice.empty();
						$('<span class="redactorAutosaveMessage" />').appendTo($autosaveNotice);
					}
				}).bind(this);
				
				$autosaveNotice.on('transitionend webkitTransitionEnd', $resetNotice);
			}
			
			var $message = '';
			switch (type) {
				case 'prompt':
					$('<span class="icon icon16 fa-info blue jsTooltip" title="' + WCF.Language.get('wcf.message.autosave.restored.version', { date: new Date(data.timestamp).toLocaleString() }) + '"></span>').prependTo($autosaveNotice);
					var $accept = $('<span class="icon icon16 fa-check green pointer jsTooltip" title="' + WCF.Language.get('wcf.message.autosave.prompt.confirm') + '"></span>').appendTo($autosaveNotice);
					var $discard = $('<span class="icon icon16 fa-times red pointer jsTooltip" title="' + WCF.Language.get('wcf.message.autosave.prompt.discard') + '"></span>').appendTo($autosaveNotice);
					
					$accept.click((function() {
						this.wutil.replaceText(data.content);
						
						$resetNotice(null);
						
						this.wutil.autosaveShowNotice('restored', data);
					}).bind(this));
					
					$discard.click((function() {
						this.wutil.autosavePurge();
						
						$autosaveNotice.removeClass('open');
					}).bind(this));
					
					$message = WCF.Language.get('wcf.message.autosave.prompt');
					$autosaveNotice.addClass('redactorAutosaveNoticeIcons');
					
					var $uuid = '';
					$uuid = WCF.System.Event.addListener('com.woltlab.wcf.redactor', 'keydown_' + this.$textarea.wcfIdentify(), (function(data) {
						WCF.System.Event.removeListener('com.woltlab.wcf.redactor', 'keydown_' + this.$textarea.wcfIdentify(), $uuid);
						
						setTimeout(function() { $autosaveNotice.removeClass('open'); }, 3000);
					}).bind(this));
				break;
				
				case 'restored':
					$('<span class="icon icon16 fa-info blue jsTooltip" title="' + WCF.Language.get('wcf.message.autosave.restored.version', { date: new Date(data.timestamp).toLocaleString() }) + '"></span>').prependTo($autosaveNotice);
					var $accept = $('<span class="icon icon16 fa-check green pointer jsTooltip" title="' + WCF.Language.get('wcf.message.autosave.restored.confirm') + '"></span>').appendTo($autosaveNotice);
					var $discard = $('<span class="icon icon16 fa-times red pointer jsTooltip" title="' + WCF.Language.get('wcf.message.autosave.restored.revert') + '"></span>').appendTo($autosaveNotice);
					
					$accept.click(function() { $autosaveNotice.removeClass('open'); });
					
					$discard.click((function() {
						WCF.System.Confirmation.show(WCF.Language.get('wcf.message.autosave.restored.revert.confirmMessage'), (function(action) {
							if (action === 'confirm') {
								this.wutil.reset();
								this.wutil.autosavePurge();
								
								$autosaveNotice.removeClass('open');
							}
						}).bind(this));
					}).bind(this));
					
					$message = WCF.Language.get('wcf.message.autosave.restored');
					$autosaveNotice.addClass('redactorAutosaveNoticeIcons');
					
					var $uuid = '';
					$uuid = WCF.System.Event.addListener('com.woltlab.wcf.redactor', 'keydown_' + this.$textarea.wcfIdentify(), (function(data) {
						WCF.System.Event.removeListener('com.woltlab.wcf.redactor', 'keydown_' + this.$textarea.wcfIdentify(), $uuid);
						
						setTimeout(function() { $accept.trigger('click'); }, 3000);
					}).bind(this));
				break;
				
				case 'saved':
					if ($autosaveNotice.hasClass('open')) {
						return;
					}
					
					setTimeout(function() {
						$autosaveNotice.removeClass('open');
					}, 2000);
					
					$message = WCF.Language.get('wcf.message.autosave.saved');
				break;
			}
			
			$autosaveNotice.children('span.redactorAutosaveMessage').text($message);
			$autosaveNotice.addClass('open');
			
			if (type !== 'saved') {
				WCF.DOMNodeInsertedHandler.execute();
			}
		},
		
		/**
		 * Automatically purges autosaved content older than 7 days.
		 */
		autosavePurgeOutdated: function() {
			var $lastChecked = 0;
			var $prefix = this.wutil.getOption('woltlab.autosave').prefix;
			var $master = $prefix + '_wcf_master';
			
			try {
				$lastChecked = localStorage.getItem($master);
			}
			catch (e) {
				console.debug("[wutil.autosavePurgeOutdated] Unable to access local storage: " + e.message);
			}
			
			if ($lastChecked === 0) {
				// unable to access local storage, skip check
				return;
			}
			
			// JavaScript timestamps are in miliseconds
			var $oneWeekAgo = Date.now() - (7 * 24 * 3600 * 1000);
			if ($lastChecked === null || $lastChecked < $oneWeekAgo) {
				var $regExp = new RegExp('^' + $prefix + '_');
				for (var $key in localStorage) {
					if ($key.match($regExp) && $key !== $master) {
						var $value = localStorage.getItem($key);
						try {
							$value = JSON.parse($value);
						}
						catch (e) {
							$value = { timestamp: 0 };
						}
						
						if ($value === null || !$value.timestamp || $value.timestamp < $oneWeekAgo) {
							try {
								localStorage.removeItem($key);
							}
							catch (e) {
								console.debug("[wutil.autosavePurgeOutdated] Unable to access local storage: " + e.message);
							}
						}
					}
				}
				
				try {
					localStorage.setItem($master, Date.now());
				}
				catch (e) {
					console.debug("[wutil.autosavePurgeOutdated] Unable to access local storage: " + e.message);
				}
			}
		},
		
		/**
		 * Temporarily pauses autosave worker.
		 */
		autosavePause: function() {
			$autosavePaused = true;
		},
		
		/**
		 * Resumes autosave worker.
		 */
		autosaveResume: function() {
			$autosavePaused = false;
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
			this.focus.setEnd();
			
			var $lastChild = this.$editor.children(':last')[0];
			if ($lastChild.tagName === 'P') {
				// sometimes the last <p> is just empty, causing the method to fail
				if ($lastChild.innerHTML === '') {
					$lastChild.remove();
					$lastChild = $(this.opts.emptyHtml).appendTo(this.$editor)[0];
				}
				
				if ($lastChild.lastChild.nodeType === Element.TEXT_NODE) {
					this.caret.set($lastChild.lastChild, $lastChild.lastChild.length, $lastChild.lastChild, $lastChild.lastChild.length);
				}
				else {
					this.caret.setEnd($lastChild);
				}
			}
			else {
				this.wutil.setCaretAfter($lastChild);
			}
			
			this.wutil.saveSelection();
		},
		
		/**
		 * Inserting block-level elements into other blocks or inline elements can mess up the entire DOM,
		 * this method tries to find the best nearby insert location.
		 */
		adjustSelectionForBlockElement: function() {
			if (document.activeElement !== this.$editor[0]) {
				this.wutil.restoreSelection();
			}
			
			if (getSelection().getRangeAt(0).collapsed) {
				var $startContainer = getSelection().getRangeAt(0).startContainer;
				if ($startContainer.nodeType === Node.TEXT_NODE && $startContainer.textContent === '\u200b' && $startContainer.parentElement && $startContainer.parentElement.tagName === 'P' && $startContainer.parentElement.parentElement === this.$editor[0]) {
					// caret position is fine
					return;
				}
				else {
					// walk tree up until we find a direct children of the editor and place the caret afterwards
					var $insertAfter = $($startContainer).parentsUntil(this.$editor[0]).last();
					if ($insertAfter[0] === document.body.parentElement) {
						// work-around if selection never has been within the editor before
						this.wutil.selectionEndOfEditor();
					}
					else {
						this.caret.setAfter($insertAfter);
						/*var $p = $('<p><br></p>').insertAfter($insertAfter);
						
						this.caret.setEnd($p);*/
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
			
			if ($range.endContainer.nodeType === Element.TEXT_NODE) {
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
			while (node && node !== this.$editor[0]) {
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
			
			value = this.wutil.addNewlines(value);
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
			var $node;
			if ((element[0] || element).parentElement && (element[0] || element).parentElement.tagName === 'BLOCKQUOTE') {
				$node = $('<div>' + this.opts.invisibleSpace + '</div>');
			}
			else {
				$node = $('<p>' + this.opts.invisibleSpace + '</p>');
			}
			
			$node[(setBefore ? 'insertBefore' : 'insertAfter')](element);
			this.caret.setEnd($node[0]);
		},
		
		/**
		 * Fixes the DOM after pasting:
		 *  - move all non-element children of the editor into a paragraph
		 *  - pasting lists/list-items in lists can yield empty <li></li>
		 */
		fixDOM: function() {
			var $current = this.$editor[0].childNodes[0];
			var $nextSibling = $current;
			var $p = null;
			
			while ($nextSibling) {
				$current = $nextSibling;
				$nextSibling = $current.nextSibling;
				
				if ($current.nodeType === Element.ELEMENT_NODE) {
					if (this.reIsBlock.test($current.tagName)) {
						$p = null;
					}
					else {
						if ($p === null) {
							$p = $('<p />').insertBefore($current);
						}
						
						$p.append($current);
					}
				}
				else if ($current.nodeType === Element.TEXT_NODE) {
					if ($p === null) {
						// check for ghost paragraphs next
						if ($nextSibling) {
							if ($nextSibling.nodeType === Element.ELEMENT_NODE && $nextSibling.tagName === 'P' && $nextSibling.innerHTML === '\u200B') {
								var $afterNextSibling = $nextSibling.nextSibling;
								this.$editor[0].removeChild($nextSibling);
								$nextSibling = $afterNextSibling;
							}
						}
						
						$p = $('<p />').insertBefore($current);
					}
					
					$p.append($current);
				}
			}
			
			var $listItems = this.$editor[0].getElementsByTagName('li');
			for (var $i = 0, $length = $listItems.length; $i < $length; $i++) {
				var $listItem = $listItems[$i];
				if (!$listItem.innerHTML.length) {
					var $parent = $listItem.parentElement;
					if ($parent.children.length > 1) {
						$listItem.parentElement.removeChild($listItem);
						
						// node list is live
						$i--;
						$length--;
					}
				}
			}
			
			// fix markup for empty lines
			for (var $i = 0, $length = this.$editor[0].children.length; $i < $length; $i++) {
				var $child = this.$editor[0].children[$i];
				if ($child.nodeType !== Node.ELEMENT_NODE || $child.tagName !== 'P') {
					// not a <p> element
					continue;
				}
				
				if ($child.innerHTML === "\n") {
					$child.parentElement.removeChild($child);
					
					$i--;
					$length--;
					
					continue;
				}
				
				if ($child.textContent.length > 0) {
					// element is non-empty
					continue;
				}
				
				if ($child.children.length > 1 || ($child.children.length === 1 && $child.children[0].tagName === 'BR')) {
					// element contains more than one children or it is just a <br>
					continue;
				}
				
				// head all the way down to the most inner node
				while ($child.children.length === 1) {
					$child = $child.children[0];
				}
				
				if ($child.childNodes.length === 0 && $child.tagName !== 'BR') {
					var $node = document.createTextNode('\u200b');
					$child.appendChild($node);
				}
			}
		}
	};
};
