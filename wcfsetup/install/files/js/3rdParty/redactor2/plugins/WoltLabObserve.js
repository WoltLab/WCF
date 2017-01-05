$.Redactor.prototype.WoltLabObserve = function() {
	"use strict";
	
	return {
		init: function () {
			// These rewritten methods below are up to 5 times faster and help prevent
			// input lags on less powerful devices. Tests have shown that just using bold
			// text on an iPad 3rd Gen causes `observe.toolbar()` to take about 30ms
			// during each keystroke with spikes at 50ms, with the delay being up to 90
			// ms on backspace.
			// 
			// The same test with the new methods average at 9ms for regular keystrokes
			// and spiking at 20ms on backspace.
			
			var buttons = elByClass('re-button', this.button.toolbar()[0]);
			this.button.setInactiveAll = (function(key) {
				var button;
				for (var i = 0, length = buttons.length; i < length; i++) {
					button = buttons[i];
					if (button.classList.contains('re-' + key)) {
						continue;
					}
					
					button.classList.remove('redactor-act');
				}
			}).bind(this);
			
			this.observe.buttons = (function(e, btnName) {
				var current = this.selection.current();
				// WoltLab modification: parent is useless for us
				//var parent = this.selection.parent();
				
				if (e !== false)
				{
					this.button.setInactiveAll();
				}
				else
				{
					this.button.setInactiveAll(btnName);
				}
				
				if (e === false && btnName !== 'html')
				{
					if ($.inArray(btnName, this.opts.activeButtons) !== -1)
					{
						this.button.toggleActive(btnName);
					}
					
					return;
				}
				
				if (!this.utils.isRedactorParent(current))
				{
					return;
				}
				
				// disable line
				if (this.utils.isCurrentOrParentHeader() || this.utils.isCurrentOrParent(['table', 'pre', 'blockquote', 'li']))
				{
					this.button.disable('horizontalrule');
				}
				else
				{
					this.button.enable('horizontalrule');
				}
				
				if (this.utils.isCurrentOrParent(['table', 'li'])) {
					this.button.disable('code');
					this.button.disable('spoiler');
					this.button.disable('woltlabQuote');
				}
				else {
					this.button.enable('code');
					this.button.enable('spoiler');
					this.button.enable('woltlabQuote');
				}
				
				// WoltLab modification: we know that there will be quite a few
				// active button states, so we'll simply check all ancestors one
				// by one instead of searching the DOM over and over again
				var editor = this.$editor[0];
				if (current.nodeType !== Node.ELEMENT_NODE) current = current.parentNode;
				
				var tagName, tags = [];
				while (current !== editor) {
					tagName = current.nodeName.toLowerCase();
					if (tags.indexOf(tagName) === -1) {
						if (this.opts.activeButtonsStates.hasOwnProperty(tagName)) {
							this.button.setActive(this.opts.activeButtonsStates[tagName]);
						}
						
						// mark as known
						tags.push(tagName);
					}
					
					current = current.parentNode;
				}
				
			}).bind(this);
			
			this.observe.dropdowns = (function() {
				var current = this.selection.current();
				var editor = this.$editor[0];
				var isRedactor = this.utils.isRedactorParent(current);
				
				var tagName, tags = [];
				if (current && isRedactor) {
					if (current.nodeType !== Node.ELEMENT_NODE) current = current.parentNode;
					
					while (current !== editor) {
						tagName = current.nodeName.toLowerCase();
						if (tags.indexOf(tagName) === -1) {
							tags.push(tagName);
						}
						
						current = current.parentNode;
					}
				}
				
				var finded = null;
				
				var value;
				for (var i = 0, length = this.opts.observe.dropdowns.length; i < length; i++) {
					value = this.opts.observe.dropdowns[i];
					
					var observe = value.observe,
					    element = observe.element,
					    $item   = value.item,
					    inValues = (observe.in) ? observe.in : false,
					    outValues = (observe.out) ? observe.out : false;
					
					if (element === 'a' && finded === null) {
						finded = $('<div />').html(this.selection.html()).find('a').length
					}
					
					if ((tags.indexOf(element) !== -1 && isRedactor) || (element === 'a' && finded !== 0)) {
						this.observe.setDropdownProperties($item, inValues, outValues);
					}
					else {
						this.observe.setDropdownProperties($item, outValues, inValues);
					}
				}
			}).bind(this);
		}
	};
};
