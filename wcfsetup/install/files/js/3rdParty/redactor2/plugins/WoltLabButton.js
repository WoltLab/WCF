$.Redactor.prototype.WoltLabButton = function() {
	"use strict";
	
	return {
		init: function() {
			// insert separators
			var button, buttonName, lastButtonName;
			for (var i = 0, length = this.opts.buttons.length; i < length; i++) {
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
			}
			
			// add tooltips
			var buttons = elByClass('re-button', this.button.toolbar()[0]), label;
			for (var i = 0, length = buttons.length; i < length; i++) {
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
