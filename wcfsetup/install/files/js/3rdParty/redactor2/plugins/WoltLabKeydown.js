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
			
			this.keydown.onBackspaceAndDeleteAfter = (function (e) {
				// remove style tag
				setTimeout($.proxy(function()
				{
					this.code.syncFire = false;
					this.keydown.removeEmptyLists();
					
					// WoltLab modification: allow style tag on `<span>`
					this.core.editor().find('*[style]').not('span, img, #redactor-image-box, #redactor-image-editter').removeAttr('style');
					
					this.keydown.formatEmpty(e);
					this.code.syncFire = true;
					
				}, this), 1);
			}).bind(this);
			
			var mpOnEnter = (function(e) {
				var stop = this.core.callback('enter', e);
				if (stop === false) {
					e.preventDefault();
					return false;
				}
				
				// blockquote exit
				if (this.keydown.blockquote && this.keydown.exitFromBlockquote(e) === true) {
					return false;
				}
				
				// pre
				if (this.keydown.pre) {
					return this.keydown.insertNewLine(e);
				}
				// blockquote & figcaption
				else if (this.keydown.blockquote || this.keydown.figcaption) {
					return this.keydown.insertBreakLine(e);
				}
				// figure
				else if (this.keydown.figure) {
					setTimeout($.proxy(function () {
						this.keydown.replaceToParagraph('FIGURE');
						
					}, this), 1);
				}
				// paragraphs
				else if (this.keydown.block) {
					setTimeout($.proxy(function () {
						this.keydown.replaceToParagraph('DIV');
						
					}, this), 1);
					
					// empty list exit
					if (this.keydown.block.tagName === 'LI') {
						var current = this.selection.current();
						var $parent = $(current).closest('li', this.$editor[0]);
						// WoltLab modification: this was a call to $.parents() that did
						// escape Redactor
						var $list = $parent.parentsUntil(this.$editor[0], 'ul,ol').last();
						
						if ($parent.length !== 0 && this.utils.isEmpty($parent.html()) && $list.next().length === 0 && this.utils.isEmpty($list.find("li").last().html())) {
							$list.find("li").last().remove();
							
							var node = $(this.opts.emptyHtml);
							$list.after(node);
							this.caret.start(node);
							
							return false;
						}
					}
					
				}
				// outside
				else if (!this.keydown.block) {
					return this.keydown.insertParagraph(e);
				}
				
				// firefox enter into inline element
				if (this.detect.isFirefox() && this.utils.isInline(this.keydown.parent)) {
					this.keydown.insertBreakLine(e);
					return;
				}
				
				// remove inline tags in new-empty paragraph
				/*
					WoltLab modification: preserve inline tags
				
				setTimeout($.proxy(function () {
					var inline = this.selection.inline();
					if (inline && this.utils.isEmpty(inline.innerHTML)) {
						var parent = this.selection.block();
						$(inline).remove();
						//this.caret.start(parent);
						
						var range = document.createRange();
						range.setStart(parent, 0);
						
						var textNode = document.createTextNode('\u200B');
						
						range.insertNode(textNode);
						range.setStartAfter(textNode);
						range.collapse(true);
						
						var sel = window.getSelection();
						sel.removeAllRanges();
						sel.addRange(range);
					}
					
				}, this), 1);
				*/
			}).bind(this);
			
			this.keydown.onEnter = (function(e) {
				var isBlockquote = this.keydown.blockquote;
				if (isBlockquote) this.keydown.blockquote = false;
				
				var returnValue = mpOnEnter.call(this, e);
				
				if (isBlockquote) this.keydown.blockquote = isBlockquote;
				
				return returnValue;
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
			
			var mpFormatEmpty = this.keydown.formatEmpty;
			this.keydown.formatEmpty = (function (e) {
				// check if there are block elements other than <p>
				var editor = this.$editor[0], node;
				for (var i = 0, length = editor.childElementCount; i < length; i++) {
					node = editor.children[i];
					if (node.nodeName !== 'P' && this.utils.isBlockTag(node.nodeName)) {
						// there is at least one block element, treat as non-empty
						return;
					}
				}
				
				return mpFormatEmpty.call(this, e);
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
								
								if (typeof submitButton.click === 'function') {
									submitButton.click();
								}
								else {
									Core.triggerEvent(submitButton, WCF_CLICK_EVENT);
								}
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
