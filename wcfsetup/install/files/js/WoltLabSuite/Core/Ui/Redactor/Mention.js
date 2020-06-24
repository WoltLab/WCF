define(['Ajax', 'Environment', 'StringUtil', 'Ui/CloseOverlay'], function(Ajax, Environment, StringUtil, UiCloseOverlay) {
	"use strict";
	
	if (!COMPILER_TARGET_DEFAULT) {
		var Fake = function() {};
		Fake.prototype = {
			init: function() {},
			_keyDown: function() {},
			_keyUp: function() {},
			_getTextLineInFrontOfCaret: function() {},
			_getDropdownMenuPosition: function() {},
			_setUsername: function() {},
			_selectMention: function() {},
			_updateDropdownPosition: function() {},
			_selectItem: function() {},
			_hideDropdown: function() {},
			_ajaxSetup: function() {},
			_ajaxSuccess: function() {}
		};
		return Fake;
	}
	
	var _dropdownContainer = null;
	
	function UiRedactorMention(redactor) { this.init(redactor); }
	UiRedactorMention.prototype = {
		init: function(redactor) {
			this._active = false;
			this._dropdownActive = false;
			this._dropdownMenu = null;
			this._itemIndex = 0;
			this._lineHeight = null;
			this._mentionStart = '';
			this._redactor = redactor;
			this._timer = null;
			
			redactor.WoltLabEvent.register('keydown', this._keyDown.bind(this));
			redactor.WoltLabEvent.register('keyup', this._keyUp.bind(this));
			
			UiCloseOverlay.add('UiRedactorMention-' + redactor.core.element()[0].id, this._hideDropdown.bind(this));
		},
		
		_keyDown: function(data) {
			if (!this._dropdownActive) {
				return;
			}
			
			/** @var Event event */
			var event = data.event;
			
			switch (event.which) {
				// enter
				case 13:
					this._setUsername(null, this._dropdownMenu.children[this._itemIndex].children[0]);
					break;
				
				// arrow up
				case 38:
					this._selectItem(-1);
					break;
				
				// arrow down
				case 40:
					this._selectItem(1);
					break;
				
				default:
					this._hideDropdown();
					return;
			}
			
			event.preventDefault();
			data.cancel = true;
		},
		
		_keyUp: function(data) {
			/** @var Event event */
			var event = data.event;
			
			// ignore return key
			if (event.which === 13) {
				this._active = false;
				
				return;
			}
			
			if (this._dropdownActive) {
				data.cancel = true;
				
				// ignore arrow up/down
				if (event.which === 38 || event.which === 40) {
					return;
				}
			}
			
			var text = this._getTextLineInFrontOfCaret();
			if (text.length > 0 && text.length < 25) {
				var match = text.match(/@([^,]{3,})$/);
				if (match) {
					// if mentioning is at text begin or there's a whitespace character
					// before the '@', everything is fine
					if (!match.index || text[match.index - 1].match(/\s/)) {
						this._mentionStart = match[1];
						
						if (this._timer !== null) {
							window.clearTimeout(this._timer);
							this._timer = null;
						}
						
						this._timer = window.setTimeout((function() {
							Ajax.api(this, {
								parameters: {
									data: {
										searchString: this._mentionStart
									}
								}
							});
							
							this._timer = null;
						}).bind(this), 500);
					}
				}
				else {
					this._hideDropdown();
				}
			}
			else {
				this._hideDropdown();
			}
		},
		
		_getTextLineInFrontOfCaret: function() {
			var data = this._selectMention(false);
			if (data !== null) {
				return data.range.cloneContents().textContent.replace(/\u200B/g, '').replace(/\u00A0/g, ' ').trim();
			}
			
			return '';
		},
		
		_getDropdownMenuPosition: function() {
			var data = this._selectMention();
			if (data === null) {
				return null;
			}
			
			this._redactor.selection.save();
			
			data.selection.removeAllRanges();
			data.selection.addRange(data.range);
			
			// get the offsets of the bounding box of current text selection
			var rect = data.selection.getRangeAt(0).getBoundingClientRect();
			var offsets = {
				top: Math.round(rect.bottom) + (window.scrollY || window.pageYOffset),
				left: Math.round(rect.left) + document.body.scrollLeft
			};
			
			if (this._lineHeight === null) {
				this._lineHeight = Math.round(rect.bottom - rect.top);
			}
			
			// restore caret position
			this._redactor.selection.restore();
			
			return offsets;
		},
		
		_setUsername: function(event, item) {
			if (event) {
				event.preventDefault();
				item = event.currentTarget;
			}
			
			var data = this._selectMention();
			if (data === null) {
				this._hideDropdown();
				
				return;
			}
			
			// allow redactor to undo this
			this._redactor.buffer.set();
			
			data.selection.removeAllRanges();
			data.selection.addRange(data.range);
			
			var range = getSelection().getRangeAt(0);
			range.deleteContents();
			range.collapse(true);
			
			// Mentions only allow for one whitespace per match, putting the username in apostrophes
			// will allow an arbitrary number of spaces.
			var username = elData(item, 'username').trim();
			if (username.split(/\s/g).length > 2) {
				username = "'" + username.replace(/'/g, "''") + "'";
			}
			
			var text = document.createTextNode('@' + username + '\u00A0');
			range.insertNode(text);
			
			range = document.createRange();
			range.selectNode(text);
			range.collapse(false);
			
			data.selection.removeAllRanges();
			data.selection.addRange(range);
			
			this._hideDropdown();
		},
		
		_selectMention: function (skipCheck) {
			var selection = window.getSelection();
			if (!selection.rangeCount || !selection.isCollapsed) {
				return null;
			}
			
			var container = selection.anchorNode;
			if (container.nodeType === Node.TEXT_NODE) {
				// work-around for Firefox after suggestions have been presented
				container = container.parentNode;
			}
			
			// check if there is an '@' within the current range
			if (container.textContent.indexOf('@') === -1) {
				return null;
			}
			
			// check if we're inside code or quote blocks
			var editor = this._redactor.core.editor()[0];
			while (container && container !== editor) {
				if (['PRE', 'WOLTLAB-QUOTE'].indexOf(container.nodeName) !== -1) {
					return null;
				}
				
				container = container.parentNode;
			}
			
			var range = selection.getRangeAt(0);
			var endContainer = range.startContainer;
			var endOffset = range.startOffset;
			
			// find the appropriate end location
			while (endContainer.nodeType === Node.ELEMENT_NODE) {
				if (endOffset === 0 && endContainer.childNodes.length === 0) {
					// invalid start location
					return null;
				}
				
				// startOffset for elements will always be after a node index
				// or at the very start, which means if there is only text node
				// and the caret is after it, startOffset will equal `1`
				endContainer = endContainer.childNodes[(endOffset ? endOffset - 1 : 0)];
				if (endOffset > 0) {
					if (endContainer.nodeType === Node.TEXT_NODE) {
						endOffset = endContainer.textContent.length;
					}
					else {
						endOffset = endContainer.childNodes.length;
					}
				}
			}
			
			var startContainer = endContainer;
			var startOffset = -1;
			while (startContainer !== null) {
				if (startContainer.nodeType !== Node.TEXT_NODE) {
					return null;
				}
				
				if (startContainer.textContent.indexOf('@') !== -1) {
					startOffset = startContainer.textContent.lastIndexOf('@');
					
					break;
				}
				
				startContainer = startContainer.previousSibling;
			}
			
			if (startOffset === -1) {
				// there was a non-text node that was in our way
				return null;
			}
			
			try {
				// mark the entire text, starting from the '@' to the current cursor position
				range = document.createRange();
				range.setStart(startContainer, startOffset);
				range.setEnd(endContainer, endOffset);
			}
			catch (e) {
				window.console.debug(e);
				return null;
			}
			
			if (skipCheck === false) {
				// check if the `@` occurs at the very start of the container
				// or at least has a whitespace in front of it
				var text = '';
				if (startOffset) {
					text = startContainer.textContent.substr(0, startOffset);
				}
				
				while (startContainer = startContainer.previousSibling) {
					if (startContainer.nodeType === Node.TEXT_NODE) {
						text = startContainer.textContent + text;
					}
					else {
						break;
					}
				}
				
				if (text.replace(/\u200B/g, '').match(/\S$/)) {
					return null;
				}
			}
			else {
				// check if new range includes the mention text
				if (range.cloneContents().textContent.replace(/\u200B/g, '').replace(/\u00A0/g, '').trim().replace(/^@/, '') !== this._mentionStart) {
					// string mismatch
					return null;
				}
			}
			
			return {
				range: range,
				selection: selection
			};
		},
		
		_updateDropdownPosition: function() {
			var offset = this._getDropdownMenuPosition();
			if (offset === null) {
				this._hideDropdown();
				
				return;
			}
			offset.top += 7; // add a little vertical gap
			
			this._dropdownMenu.style.setProperty('left', offset.left + 'px', '');
			this._dropdownMenu.style.setProperty('top', offset.top + 'px', '');
			
			this._selectItem(0);
			
			if (offset.top + this._dropdownMenu.offsetHeight + 10 > window.innerHeight + (window.scrollY || window.pageYOffset)) {
				this._dropdownMenu.style.setProperty('top', offset.top - this._dropdownMenu.offsetHeight - 2 * this._lineHeight + 7 + 'px', '');
			}
		},
		
		_selectItem: function(step) {
			// find currently active item
			var item = elBySel('.active', this._dropdownMenu);
			if (item !== null) {
				item.classList.remove('active');
			}
			
			this._itemIndex += step;
			if (this._itemIndex < 0) {
				this._itemIndex = this._dropdownMenu.childElementCount - 1;
			}
			else if (this._itemIndex >= this._dropdownMenu.childElementCount) {
				this._itemIndex = 0;
			}
			
			this._dropdownMenu.children[this._itemIndex].classList.add('active');
		},
		
		_hideDropdown: function() {
			if (this._dropdownMenu !== null) this._dropdownMenu.classList.remove('dropdownOpen');
			this._dropdownActive = false;
			this._itemIndex = 0;
		},
		
		_ajaxSetup: function() {
			return {
				data: {
					actionName: 'getSearchResultList',
					className: 'wcf\\data\\user\\UserAction',
					interfaceName: 'wcf\\data\\ISearchAction',
					parameters: {
						data: {
							includeUserGroups: true,
							scope: 'mention'
						}
					}
				},
				silent: true
			};
		},
		
		_ajaxSuccess: function(data) {
			if (!Array.isArray(data.returnValues) || !data.returnValues.length) {
				this._hideDropdown();
				
				return;
			}
			
			if (this._dropdownMenu === null) {
				this._dropdownMenu = elCreate('ol');
				this._dropdownMenu.className = 'dropdownMenu';
				
				if (_dropdownContainer === null) {
					_dropdownContainer = elCreate('div');
					_dropdownContainer.className = 'dropdownMenuContainer';
					document.body.appendChild(_dropdownContainer);
				}
				
				_dropdownContainer.appendChild(this._dropdownMenu);
			}
			
			this._dropdownMenu.innerHTML = '';
			
			var callbackClick = this._setUsername.bind(this), link, listItem, user;
			for (var i = 0, length = data.returnValues.length; i < length; i++) {
				user = data.returnValues[i];
				
				listItem = elCreate('li');
				link = elCreate('a');
				link.addEventListener('mousedown', callbackClick);
				link.className = 'box16';
				link.innerHTML = '<span>' + user.icon + '</span> <span>' + StringUtil.escapeHTML(user.label) + '</span>';
				elData(link, 'user-id', user.objectID);
				elData(link, 'username', user.label);
				
				listItem.appendChild(link);
				this._dropdownMenu.appendChild(listItem);
			}
			
			this._dropdownMenu.classList.add('dropdownOpen');
			this._dropdownActive = true;
			
			this._updateDropdownPosition();
		}
	};
	
	return UiRedactorMention;
});
