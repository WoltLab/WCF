define(['Ajax', 'Environment', 'Ui/CloseOverlay'], function(Ajax, Environment, UiCloseOverlay) {
	"use strict";
	
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
					break;
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
			if (text.length) {
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
			/** @var Range range */
			var range = window.getSelection().getRangeAt(0);
			if (!range.collapsed) {
				return '';
			}
			
			// in Firefox, blurring and refocusing the browser creates separate text nodes
			if (Environment.browser() === 'firefox' && range.startContainer.nodeType === Node.TEXT_NODE) {
				range.startContainer.parentNode.normalize();
			}
			
			var text = range.startContainer.textContent.substr(0, range.startOffset);
			
			return text.replace(/\u200B/g, '').replace(/\u00A0/g, '');
		},
		
		_getDropdownMenuPosition: function() {
			var data = this._selectMention();
			if (data === null) {
				return null;
			}
			
			this._redactor.selection.save();
			
			data.selection.removeAllRanges();
			data.selection.addRange(data.newRange);
			
			// get the offsets of the bounding box of current text selection
			var rect = data.selection.getRangeAt(0).getBoundingClientRect();
			var offsets = {
				top: Math.round(rect.bottom) + window.scrollY,
				left: Math.round(rect.left) + document.body.scrollLeft
			};
			
			if (this._lineHeight === null) {
				this._lineHeight = Math.round(rect.bottom - rect.top - window.scrollY);
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
			data.selection.addRange(data.newRange);
			
			var range = getSelection().getRangeAt(0);
			range.deleteContents();
			range.collapse(true);
			
			var text = document.createTextNode('@' + elData(item, 'username') + ' ');
			range.insertNode(text);
			
			range = document.createRange();
			range.selectNode(text);
			range.collapse(false);
			
			data.selection.removeAllRanges();
			data.selection.addRange(range);
			
			this._hideDropdown();
		},
		
		_selectMention: function () {
			var selection = window.getSelection();
			if (!selection.rangeCount || !selection.isCollapsed) {
				return null;
			}
			
			var originalRange = selection.getRangeAt(0).cloneRange();
			
			// mark the entire text, starting from the '@' to the current cursor position
			var newRange = document.createRange();
			
			var startContainer = originalRange.startContainer;
			var startOffset = originalRange.startOffset - (this._mentionStart.length + 1);
			
			if (startContainer.nodeType === Node.ELEMENT_NODE) {
				startContainer = startContainer.childNodes[originalRange.startOffset];
				startOffset = startContainer.length - (this._mentionStart.length + 1);
			}
			
			// navigating with the keyboard before hitting enter will cause the text node to be split
			if (startOffset < 0) {
				startContainer = startContainer.previousSibling;
				if (!startContainer || startContainer.nodeType !== Node.TEXT_NODE) {
					// selection is no longer where it used to be
					return null;
				}
				
				startOffset = startContainer.length - (this._mentionStart.length + 1) - (originalRange.startOffset - 1);
			}
			
			try {
				newRange.setStart(startContainer, startOffset);
				newRange.setEnd(originalRange.startContainer, originalRange.startOffset);
			}
			catch (e) {
				console.debug(e);
				return null;
			}
			
			// check if new range includes the mention text
			var div = elCreate('div');
			div.appendChild(newRange.cloneContents());
			if (div.textContent.replace(/\u200B/g, '').replace(/\u00A0/g, '').trim().replace(/^@/, '') !== this._mentionStart) {
				// string mismatch
				return null;
			}
			
			return {
				newRange: newRange,
				originalRange: originalRange,
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
			
			if (offset.top + this._dropdownMenu.offsetHeight + 10 > window.innerHeight + window.scrollY) {
				this._dropdownMenu.classList.add('dropdownArrowBottom');
				
				this._dropdownMenu.style.setProperty('top', offset.top - this._dropdownMenu.offsetHeight - 2 * this._lineHeight + 7 + 'px', '');
			}
			else {
				this._dropdownMenu.classList.remove('dropdownArrowBottom');
			}
		},
		
		_selectItem: function(step) {
			// find currently active item
			var item = elBySel('.active', this._dropdownMenu);
			if (item !== null) {
				item.classList.remove('active');
			}
			
			this._itemIndex += step;
			if (this._itemIndex === -1) {
				this._itemIndex = this._dropdownMenu.childElementCount - 1;
			}
			else if (this._itemIndex === this._dropdownMenu.childElementCount) {
				this._itemIndex = 0;
			}
			
			this._dropdownMenu.children[this._itemIndex].classList.add('active');
		},
		
		_hideDropdown: function() {
			if (this._dropdownMenu !== null) this._dropdownMenu.classList.remove('dropdownOpen');
			this._dropdownActive = false;
		},
		
		_ajaxSetup: function() {
			return {
				data: {
					actionName: 'getSearchResultList',
					className: 'wcf\\data\\user\\UserAction',
					interfaceName: 'wcf\\data\\ISearchAction',
					parameters: {
						data: {
							includeUserGroups: false
						}
					}
				}
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
				link.addEventListener(WCF_CLICK_EVENT, callbackClick);
				link.className = 'box16';
				link.innerHTML = '<span>' + user.icon + '</span> <span>' + user.label + '</span>';
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
