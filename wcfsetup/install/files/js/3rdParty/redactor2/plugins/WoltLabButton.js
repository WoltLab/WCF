$.Redactor.prototype.WoltLabButton = function() {
	"use strict";
	
	var _toggleButton;
	
	return {
		init: function() {
			// add custom buttons
			var button, buttonName, i, length;
			//noinspection JSUnresolvedVariable
			for (i = 0, length = this.opts.woltlab.customButtons.length; i < length; i++) {
				//noinspection JSUnresolvedVariable
				buttonName = this.opts.woltlab.customButtons[i];
				
				button = this.button.add(buttonName, '');
				this.button.addCallback(button, this.WoltLabButton._handleCustomButton);
			}
			
			// set button icons and labels
			var buttonData;
			for (i = 0, length = this.opts.buttons.length; i < length; i++) {
				buttonName = this.opts.buttons[i];
				
				if (buttonName === 'wcfSeparator') {
					// separators will be inserted in the next step
					continue;
				}
				
				//noinspection JSUnresolvedVariable
				if (!this.opts.woltlab.buttons.hasOwnProperty(buttonName)) {
					throw new Error("Missing button definition for '" + buttonName + "'.");
				}
				
				//noinspection JSUnresolvedVariable
				buttonData = this.opts.woltlab.buttons[buttonName];
				if (buttonName === 'underline') {
					this.opts.activeButtonsStates.u = 'underline'
				}
				
				switch (buttonName) {
					case 'subscript':
					case 'superscript':
						button = this.button.addAfter(this.opts.buttons[i - 1], buttonName, '');
						this.button.setEvent(button, buttonName, { func: 'inline.format' });
						
						this.opts.activeButtonsStates[(buttonName === 'subscript' ? 'sub' : 'sup')] = buttonName;
						
						break;
					
					case 'redo':
					case 'undo':
						button = this.button.addAfter(this.opts.buttons[i - 1], buttonName, '');
						this.button.addCallback(button, this.buffer[buttonName]);
						break;
					
					default:
						button = this.button.get(buttonName);
						break;
				}
				
				// set icon
				this.button.setIcon(button, '<span class="icon icon16 ' + buttonData.icon + '"></span>');
				if (!button[0]) {
					throw new Error("Missing button element for '" + buttonName + "'.");
				}
				// set title
				//noinspection JSUnresolvedVariable
				elAttr(button[0], 'title', buttonData.title);
				button[0].classList.add('jsTooltip');
			}
			
			// enforce button order as provided with `opts.buttons`
			var listItem, toolbarButtons = {}, toolbarOrder = [], toolbar = this.core.toolbar()[0];
			while (toolbar.childElementCount) {
				listItem = toolbar.removeChild(toolbar.children[0]);
				buttonName = elAttr(listItem.children[0], 'rel');
				
				toolbarButtons[buttonName] = listItem;
				toolbarOrder.push(buttonName);
			}
			
			var addSeparator = false;
			for (i = 0, length = this.opts.buttons.length; i < length; i++) {
				buttonName = this.opts.buttons[i];
				
				if (buttonName === 'wcfSeparator') {
					addSeparator = true;
					continue;
				}
				
				listItem = toolbarButtons[buttonName];
				toolbar.appendChild(listItem);
				toolbarOrder.splice(toolbarOrder.indexOf(buttonName), 1);
				
				if (addSeparator) {
					listItem.classList.add('redactor-toolbar-separator');
					addSeparator = false;
				}
			}
			
			// button mobile visibility
			for (i = 0, length = toolbar.childElementCount; i < length; i++) {
				listItem = toolbar.children[i];
				button = listItem.children[0];
				
				elData(listItem, 'show-on-mobile', (this.opts.woltlab.buttonMobile.indexOf(button.rel) !== -1));
			}
			
			// add remaining elements
			toolbarOrder.forEach(function(buttonName) {
				toolbar.appendChild(toolbarButtons[buttonName]);
			});
			
			WCF.DOMNodeInsertedHandler.execute();
			
			require(['Ui/Screen'], (function (UiScreen) {
				UiScreen.on('screen-xs', {
					match: this.WoltLabButton._enableToggleButton.bind(this),
					unmatch: this.WoltLabButton._disableToggleButton.bind(this),
					setup: this.WoltLabButton._setupToggleButton.bind(this)
				});
			}).bind(this));
		},
		
		_handleCustomButton: function (bbcode) {
			var data = { cancel: false };
			WCF.System.Event.fireEvent('com.woltlab.wcf.redactor2', 'bbcode_' + bbcode + '_' + this.$element[0].id, data);
			
			if (data.cancel !== true) {
				this.buffer.set();
				
				var html = '[' + bbcode + ']' + this.selection.html() + (this.selection.is() ? '' : this.marker.html()) + '[/' + bbcode + ']';
				this.insert.html(html);
				this.selection.restore();
			}
			
			window.setTimeout((function () {
				if (document.activeElement !== this.$editor[0]) {
					this.$editor[0].focus();
				}
			}).bind(this), 10);
		},
		
		_enableToggleButton: function () {
			if (_toggleButton.parentNode === null) {
				this.$toolbar[0].appendChild(_toggleButton);
			}
		},
		
		_disableToggleButton: function () {
			if (_toggleButton.parentNode !== null) {
				this.$toolbar[0].removeChild(_toggleButton);
			}
		},
		
		_setupToggleButton: function () {
			_toggleButton = elCreate('li');
			_toggleButton.className = 'redactorToolbarToggle';
			_toggleButton.innerHTML = '<a href="#"><span class="icon icon16 fa-caret-down"></span></a>';
			elData(_toggleButton, 'show-on-mobile', true);
			
			var icon = _toggleButton.children[0].children[0];
			var toggle = (function (event) {
				if (event instanceof Event) {
					event.preventDefault();
				}
				
				if (this.$toolbar[0].classList.toggle('redactorToolbarOverride')) {
					// this prevents mobile browser from refocusing another element
					if (document.activeElement && document.activeElement !== this.$editor[0]) {
						document.activeElement.blur();
					}
				}
				
				icon.classList.toggle('fa-caret-down');
				icon.classList.toggle('fa-caret-up');
			}).bind(this);
			
			_toggleButton.children[0].addEventListener('mousedown', toggle);
			
			this.$toolbar[0].appendChild(_toggleButton);
			
			WCF.System.Event.addListener('com.woltlab.wcf.redactor2', 'reset_' + this.$element[0].id, (function () {
				if (this.$toolbar[0].classList.contains('redactorToolbarOverride')) {
					toggle();
				}
			}).bind(this));
		}
	};
};
