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
					if (button.rel !== 'html') elAttr(button, 'tabindex', -1);
					elAttr(button, 'aria-pressed', false);
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
				
				var isSource = this.WoltLabSource.isActive();
				
				// disable line
				if (this.utils.isCurrentOrParentHeader() || this.utils.isCurrentOrParent(['table', 'pre', 'blockquote', 'li']))
				{
					this.button.disable('horizontalrule');
				}
				else if (!isSource)
				{
					this.button.enable('horizontalrule');
				}
				
				if (this.utils.isCurrentOrParent(['table', 'li'])) {
					this.button.disable('code');
					this.button.disable('spoiler');
					this.button.disable('woltlabHtml');
					this.button.disable('woltlabQuote');
				}
				else if (!isSource) {
					this.button.enable('code');
					this.button.enable('spoiler');
					this.button.enable('woltlabHtml');
					this.button.enable('woltlabQuote');
				}
				
				if (isSource) {
					this.button.setActive('html');
				}
				
				if (this.core.box()[0].classList.contains('redactorBoxFullscreen')) {
					this.button.setActive('woltlabFullscreen');
				}
				
				// WoltLab modification: we know that there will be quite a few
				// active button states, so we'll simply check all ancestors one
				// by one instead of searching the DOM over and over again
				var editor = this.$editor[0];
				if (current.nodeType !== Node.ELEMENT_NODE) current = current.parentNode;
				
				if (current.closest('.redactor-layer') === editor) {
					var key, tagName, tags = [];
					while (current !== editor) {
						tagName = current.nodeName.toLowerCase();
						if (tags.indexOf(tagName) === -1) {
							key = tagName;
							if (tagName === 'pre' && current.classList.contains('woltlabHtml')) {
								key = 'woltlab-html';
							}
							
							if (this.opts.activeButtonsStates.hasOwnProperty(key)) {
								this.button.setActive(this.opts.activeButtonsStates[key]);
							}
							
							// mark as known
							if (tagName !== 'pre') tags.push(tagName);
						}
						
						current = current.parentNode;
					}
				}
			}).bind(this);
			
			this.observe.dropdowns = (function() {
				var current = this.selection.current();
				if (current && current.nodeType !== Node.ELEMENT_NODE) current = current.parentNode;
				
				var editor = this.$editor[0];
				var isRedactor = (current && current.closest('.redactor-layer') === editor);
				
				var tagName, tags = [];
				if (isRedactor) {
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
