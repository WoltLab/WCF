$.Redactor.prototype.WoltLabKeydown = function() {
	"use strict";
	
	var _tags = [];
	
	return {
		init: function () {
			this.keydown.onArrowDown = (function() {
				var tags = this.WoltLabKeydown._getBlocks();
				
				for (var i = 0; i < tags.length; i++) {
					if (tags[i]) {
						this.keydown.insertAfterLastElement(tags[i]);
						return false;
					}
				}
			}).bind(this);
			
			this.keydown.onArrowUp = (function() {
				var tags = this.WoltLabKeydown._getBlocks();
				
				for (var i = 0; i < tags.length; i++) {
					if (tags[i]) {
						this.keydown.insertBeforeFirstElement(tags[i]);
						return false;
					}
				}
			}).bind(this);
			
			var mpOnEnter = this.keydown.onEnter;
			this.keydown.onEnter = (function(e) {
				var isBlockquote = this.keydown.blockquote;
				if (isBlockquote) this.keydown.blockquote = false;
				
				mpOnEnter.call(this, e);
				
				if (isBlockquote) this.keydown.blockquote = isBlockquote;
			}).bind(this);
			
			this.keydown.replaceToParagraph = (function(tag) {
				var blockElem = this.selection.block();
				
				var blockHtml = blockElem.innerHTML.replace(/<br\s?\/?>/gi, '');
				if (blockElem.tagName === tag && this.utils.isEmpty(blockHtml) && !$(blockElem).hasClass('redactor-in'))
				{
					var p = document.createElement('p');
					$(blockElem).replaceWith(p);
					
					// caret to p
					var range = document.createRange();
					range.setStart(p, 0);
					
					var textNode = document.createTextNode('\u200B');
					
					range.insertNode(textNode);
					range.setStartAfter(textNode);
					range.collapse(true);
					
					var sel = window.getSelection();
					sel.removeAllRanges();
					sel.addRange(range);
					
					return false;
				}
				else if (blockElem.tagName === 'P')
				{
					// WoltLab modification: do not remove class, preserving
					// text alignment
					$(blockElem)/*.removeAttr('class')*/.removeAttr('style');
				}
			}).bind(this);
			
			this.keydown.onShiftEnter = (function(e) {
				this.buffer.set();
				
				if (this.keydown.pre) {
					return this.keydown.insertNewLine(e);
				}
				
				return this.insert.raw('<br>\u200B');
			}).bind(this);
			
			var mpOnTab = this.keydown.onTab;
			this.keydown.onTab = (function(e, key) {
				if (!this.keydown.pre && $(this.selection.current()).closest('ul, ol', this.core.editor()[0]).length === 0) {
					return true;
				}
				
				return mpOnTab.call(this, e, key);
			}).bind(this);
			
			require(['Core', 'Environment'], (function (Core, Environment) {
				if (Environment.platform() !== 'desktop') {
					// ignore mobile devices
					return;
				}
				
				var container = this.$editor[0].closest('form, .message');
				if (container === null) return;
				
				var formSubmit = elBySel('.formSubmit', container);
				if (formSubmit === null) return;
				
				var submitButton = elBySel('input[type="submit"], button[data-type="save"], button[accesskey="s"]', formSubmit);
				if (submitButton) {
					// remove access key
					submitButton.removeAttribute('accesskey');
					
					// mimic the same behavior which will also work with more
					// than one editor instance on the page
					this.WoltLabEvent.register('keydown', (function (data) {
						// 83 = [S]
						if (data.event.which === 83) {
							var submit = false;
							
							if (window.navigator.platform.match(/^Mac/)) {
								if (data.event.ctrlKey && data.event.altKey) {
									submit = true;
								}
							}
							else if (data.event.altKey && !data.event.ctrlKey) {
								submit = true;
							}
							
							if (submit) {
								data.cancel = true;
								
								Core.triggerEvent(submitButton, WCF_CLICK_EVENT);
							}
						}
					}).bind(this));
				}
			}).bind(this));
			
		},
		
		register: function (tag) {
			if (_tags.indexOf(tag) === -1) {
				_tags.push(tag);
			}
		},
		
		_getBlocks: function () {
			var tags = [this.keydown.blockquote, this.keydown.pre, this.keydown.figcaption];
			
			for (var i = 0, length = _tags.length; i < length; i++) {
				tags.push(this.utils.isTag(this.keydown.current, _tags[i]))
			}
			
			return tags;
		}
	}
};
