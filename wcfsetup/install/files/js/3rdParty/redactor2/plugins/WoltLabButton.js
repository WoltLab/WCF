$.Redactor.prototype.WoltLabButton = function() {
	"use strict";
	
	return {
		init: function() {
			// set button icons and labels
			var button, buttonData, buttonName;
			for (var i = 0, length = this.opts.buttons.length; i < length; i++) {
				buttonName = this.opts.buttons[i];
				
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
				
				// set title
				//noinspection JSUnresolvedVariable
				elAttr(button[0], 'title', buttonData.title);
				button[0].classList.add('jsTooltip');
			}
			
			WCF.DOMNodeInsertedHandler.execute();
			
			return;
			var button, buttonName, i, lastButtonName = '', length;
			
			// add missing buttons
			for (i = 0, length = this.opts.buttons.length; i < length; i++) {
				buttonName = this.opts.buttons[i];
				
				// check if button exists
				button = this.button.get(buttonName);
				if (button.length === 0) {
					button = this.button.addAfter(lastButtonName, buttonName, this.opts.lang[buttonName]);
					
					if (buttonName === 'subscript' || buttonName === 'superscript') {
						this.button.setEvent(button, buttonName, {func: 'inline.format'});
					}
				}
				
				if (_icons.hasOwnProperty(buttonName)) {
					this.button.setIcon(button, '<span class="icon icon16 ' + _icons[buttonName] + '"></span>');
				}
				
				lastButtonName = buttonName;
			}
			
			
			// insert separators
			/*var button, buttonName, lastButtonName, i, length;
			for (i = 0, length = this.opts.buttons.length; i < length; i++) {
				buttonName = this.opts.buttons[i];
				
				if (buttonName === 'wcfSeparator') {
					if (lastButtonName) {
						button = this.button.get(lastButtonName);
						button[0].parentNode.classList.add('separator');
					}
					
					lastButtonName = '';
				}
				else {
					lastButtonName = buttonName;
				}
			}*/
			
			// add tooltips
			var buttons = elByClass('re-button', this.button.toolbar()[0]), label;
			for (i = 0, length = buttons.length; i < length; i++) {
				button = buttons[i];
				label = elAttr(button, 'aria-label');
				
				// check if label equals button text
				if (button.innerText.indexOf(label) !== -1) {
					continue;
				}
				
				elAttr(button, 'title', label);
				button.classList.add('jsTooltip');
			}
			
			WCF.DOMNodeInsertedHandler.execute();
		}
	};
};
