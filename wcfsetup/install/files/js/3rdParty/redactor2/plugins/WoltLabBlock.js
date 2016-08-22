$.Redactor.prototype.WoltLabBlock = function() {
	"use strict";
	
	return {
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
			
			var mpFormatCollapsed = this.block.formatCollapsed;
			this.block.formatCollapsed = (function(tag, attr, value, type) {
				var replaced = mpFormatCollapsed.call(this, tag, attr, value, type);
				
				for (var i = 0, length = replaced.length; i < length; i++) {
					this.WoltLabBlock._paragraphize(replaced[i]);
				}
				
				return replaced;
			}).bind(this);
			
			var mpFormatUncollapsed = this.block.formatUncollapsed;
			this.block.formatUncollapsed = (function(tag, attr, value, type) {
				var replaced = mpFormatUncollapsed.call(this, tag, attr, value, type);
				
				var block, firstBlock = null;
				for (var i = 0, length = replaced.length; i < length; i++) {
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
				
				return $(firstBlock);
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
			
			var paragraph = elCreate('p');
			while (block.childNodes.length) {
				paragraph.appendChild(block.childNodes[0]);
			}
			
			block.appendChild(paragraph);
		}
	}
};
