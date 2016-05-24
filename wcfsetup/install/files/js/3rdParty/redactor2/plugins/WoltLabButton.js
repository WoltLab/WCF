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
			
			// enforce button order as provided with `opts.buttons`
			var listItem, toolbarButtons = {}, toolbarOrder = [], toolbar = this.core.toolbar()[0];
			while (toolbar.childElementCount) {
				listItem = toolbar.removeChild(toolbar.children[0]);
				buttonName = elAttr(listItem.children[0], 'rel');
				
				toolbarButtons[buttonName] = listItem;
				toolbarOrder.push(buttonName);
			}
			
			for (i = 0, length = this.opts.buttons.length; i < length; i++) {
				buttonName = this.opts.buttons[i];
				
				toolbar.appendChild(toolbarButtons[buttonName]);
				toolbarOrder.splice(toolbarOrder.indexOf(buttonName), 1);
			}
			
			// add remaining elements
			toolbarOrder.forEach(function(buttonName) {
				toolbar.appendChild(toolbarButtons[buttonName]);
			});
			
			WCF.DOMNodeInsertedHandler.execute();
		}
	};
};
