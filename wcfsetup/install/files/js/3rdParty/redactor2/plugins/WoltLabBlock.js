$.Redactor.prototype.WoltLabBlock = function() {
	"use strict";
	
	return {
		preserveBlocks: ['pre', 'woltlab-quote', 'woltlab-spoiler'],
		
		init: function() {
			this.block.tags = ['p', 'blockquote', 'pre', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'figure'];
			
			this.block.format = (function(tag, attr, value, type) {
				tag = (tag === 'quote') ? 'blockquote' : tag;
				
				// WoltLab modification: move list of allowed elements
				// outside this method to allow extending it
				//
				//this.block.tags = ['p', 'blockquote', 'pre', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'figure'];
				if ($.inArray(tag, this.block.tags) === -1)
				{
					return;
				}
				
				if (tag === 'p' && typeof attr === 'undefined')
				{
					// remove all
					attr = 'class';
				}
				
				this.placeholder.hide();
				this.buffer.set();
				
				return (this.utils.isCollapsed()) ? this.block.formatCollapsed(tag, attr, value, type) : this.block.formatUncollapsed(tag, attr, value, type);
			}).bind(this);
			
			var isCaretInsideRedactor = (function (editor, block) {
				return !(document.activeElement !== editor || block === false || !this.utils.isRedactorParent(block));
			}).bind(this);
			
			var mpFormatCollapsed = this.block.formatCollapsed;
			this.block.formatCollapsed = (function(tag, attr, value, type) {
				var block = this.selection.block();
				if (block && (block.nodeName === 'LI' || block.nodeName === 'TD')) {
					// tables/lists cannot contain other block elements
					return;
				}
				
				var editor = this.core.editor()[0];
				if (!isCaretInsideRedactor(editor, block)) {
					this.selection.restore();
					
					if (document.activeElement !== editor) {
						editor.focus();
					}
				}
				
				if (!isCaretInsideRedactor(editor, block)) {
					this.focus.end();
					this.selection.save();
				}
				
				var replaced = mpFormatCollapsed.call(this, tag, attr, value, type);
				
				var length = replaced.length;
				if (length === 1 && replaced[0].nodeName.match(/^H[1-6]$/)) {
					var hX = replaced[0];
					// <hX><br></hX> behaves weird
					if (hX.childElementCount === 1 && hX.children[0].nodeName === 'BR' && this.utils.isEmpty(hX.innerHTML)) {
						hX.innerHTML = '\u200B';
					}
				}
				else {
					for (var i = 0; i < length; i++) {
						this.WoltLabBlock._paragraphize(replaced[i]);
					}
				}
				
				this.caret.end(replaced);
				
				return replaced;
			}).bind(this);
			
			this.block.formatUncollapsed = (function(tag, attr, value, type) {
				var block, i;
				this.selection.save();
				
				this.selection.blocks().forEach(function(block) {
					if (block.nodeName === 'OL' || block.nodeName === 'UL') {
						if (block.parentNode.nodeName.toLowerCase() === tag) {
							//return;
						}
						
						var div = elCreate('div');
						block.parentNode.insertBefore(div, block);
						div.appendChild(block);
					}
				});
				
				this.selection.restore();
				
				this.selection.save();
				
				var replaced = [];
				var blocks = this.selection.blocks();
				
				if (blocks[0] && ($(blocks[0]).hasClass('redactor-in') || $(blocks[0]).hasClass(
					'redactor-box'))) {
					blocks = this.core.editor().find(this.opts.blockTags.join(', '));
				}
				
				var len = blocks.length;
				for (i = 0; i < len; i++) {
					var currentTag = blocks[i].tagName.toLowerCase();
					if ($.inArray(currentTag, this.block.tags) !== -1 && currentTag !== 'figure') {
						if (tag !== 'pre' && this.WoltLabBlock.preserveBlocks.indexOf(blocks[i].nodeName.toLowerCase()) !== -1) {
							block = elCreate(tag);
							blocks[i].parentNode.insertBefore(block, blocks[i]);
							block.appendChild(blocks[i]);
							block = $(block);
						}
						else {
							block = this.utils.replaceToTag(blocks[i], tag);
						}
						
						if (typeof attr === 'object') {
							type = value;
							for (var key in attr) {
								block = this.block.setAttr(block, key, attr[key], type);
							}
						}
						else {
							block = this.block.setAttr(block, attr, value, type);
						}
						
						replaced.push(block);
						this.block.removeInlineTags(block);
					}
				}
				
				this.selection.restore();
				
				// combine pre
				if (tag === 'pre' && replaced.length !== 0) {
					var first = replaced[0];
					$.each(replaced, function (i, s) {
						if (i !== 0) {
							$(first).append("\n" + $.trim(s.html()));
							$(s).remove();
						}
					});
					
					replaced = [];
					replaced.push(first);
				}
				
				var firstBlock = null;
				for (i = 0, length = replaced.length; i < length; i++) {
					block = replaced[i][0];
					
					this.WoltLabBlock._paragraphize(block);
					
					if (i === 0) {
						firstBlock = block;
					}
					else {
						while (block.childNodes.length) {
							firstBlock.appendChild(block.childNodes[0]);
						}
						
						elRemove(block);
					}
				}

				// Convert any `<br>` inside `<pre>` with a plain newline character.
				if (firstBlock) {
					elBySelAll('br', firstBlock, function(br) {
						br.parentNode.insertBefore(document.createTextNode('\n'), br);
						elRemove(br);
					});
				}
				
				return $(firstBlock);
			}).bind(this);
			
			this.block.removeAllAttr = (function(block) {
				block = this.block.getBlocks(block);
				
				var returned = [];
				$.each(block, function(i,s)
				{
					if (typeof s.attributes === 'undefined')
					{
						returned.push(s);
					}
					
					// WoltLab fix: `attributes` is a live collection
					while (s.attributes.length) {
						s.removeAttribute(s.attributes[0].name);
					}
					
					returned.push(s);
				});
				
				return returned;
			}).bind(this);
			
			this.block.getBlocks = (function(block) {
				block = (typeof block === 'undefined') ? this.selection.blocks() : block;
				
				// Firefox may add the editor itself to the selection
				if ($(block).hasClass('redactor-box') || $(block).hasClass('redactor-layer')) {
					var blocks = [];
					var nodes = this.core.editor().children();
					$.each(nodes, $.proxy(function (i, node) {
						if (this.utils.isBlock(node)) {
							blocks.push(node);
						}
						
					}, this));
					
					return blocks;
				}
				
				return block
			}).bind(this);
		},
		
		register: function(tag, arrowKeySupport) {
			if (this.block.tags.indexOf(tag) !== -1) {
				return;
			}
			
			this.block.tags.push(tag);
			this.opts.paragraphizeBlocks.push(tag);
			
			if (this.opts.blockTags.indexOf(tag) === -1) {
				this.opts.blockTags.push(tag);
				
				this.reIsBlock = new RegExp('^(' + this.opts.blockTags.join('|').toUpperCase() + ')$', 'i');
			}
			
			if (arrowKeySupport) {
				this.WoltLabKeydown.register(tag);
			}
		},
		
		_paragraphize: function (block) {
			if (['p', 'pre', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'figure'].indexOf(block.nodeName.toLowerCase()) !== -1) {
				// do not paragraphize these blocks
				return;
			}
			
			// Check if there is at least one child that is not a block itself.
			var child, hasNonBlocks = false;
			for (var i = 0, length = block.childNodes.length; i < length; i++) {
				child = block.childNodes[i];
				if (child.nodeType === Node.TEXT_NODE) {
					hasNonBlocks = true;
					break;
				}
				else if (child.nodeType === Node.ELEMENT_NODE && this.block.tags.indexOf(child.nodeName.toLowerCase()) === -1) {
					hasNonBlocks = true;
					break;
				}
			}
			
			if (!hasNonBlocks) {
				return;
			}
			
			var node = block.childNodes[0], nextNode, p = null;
			while (node) {
				nextNode = node.nextSibling;
				if (node.nodeType !== Node.ELEMENT_NODE || this.block.tags.indexOf(node.nodeName.toLowerCase()) === -1) {
					if (p === null) {
						p = elCreate('p');
						block.insertBefore(p, node);
					}
					
					p.appendChild(node);
				}
				else if (p !== null) {
					p = null;
				}
				
				node = nextNode;
			}
		}
	}
};
