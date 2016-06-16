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
		},
		
		register: function(tag) {
			if (this.block.tags.indexOf(tag) !== -1) {
				return;
			}
			
			this.block.tags.push(tag);
			
			if (this.opts.blockTags.indexOf(tag) === -1) {
				this.opts.blockTags.push(tag);
				
				this.reIsBlock = new RegExp('^(' + this.opts.blockTags.join('|').toUpperCase() + ')$', 'i');
			}
		}
	}
};
