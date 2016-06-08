$.Redactor.prototype.WoltLabButton = function() {
	"use strict";
	
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
				
				if (buttonName === 'subscript' || buttonName === 'superscript') {
					button = this.button.addAfter(this.opts.buttons[i - 1], buttonName, '');
					this.button.setEvent(button, buttonName, { func: 'inline.format' });
				}
				else {
					button = this.button.get(buttonName);
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
			
			// add remaining elements
			toolbarOrder.forEach(function(buttonName) {
				toolbar.appendChild(toolbarButtons[buttonName]);
			});
			
			WCF.DOMNodeInsertedHandler.execute();
		},
		
		_handleCustomButton: function (bbcode) {
			var data = { cancel: false };
			WCF.System.Event.fireEvent('com.woltlab.wcf.redactor2', 'bbcode_' + bbcode + '_' + this.$element[0].id, data);
			
			if (data.cancel === true) {
				return;
			}
			
			this.buffer.set();
			
			var html = '[' + bbcode + ']' + this.selection.html() + (this.selection.is() ? '' : this.marker.html()) + '[/' + bbcode + ']';
			this.insert.html(html);
			this.selection.restore();
		}
	};
};
