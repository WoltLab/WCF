$.Redactor.prototype.WoltLabParagraphize = function() {
	"use strict";
	
	return {
		init: function () {
			this.paragraphize.getSafes = (function (html) {
				var $div = $('<div />').append(html);
				
				// WoltLab modification: do not remove <p> inside quotes
				// remove paragraphs in blockquotes
				/*$div.find('blockquote p').replaceWith(function()
				{
					return $(this).append('<br />').contents();
				});*/
				
				$div.find(this.opts.paragraphizeBlocks.join(', ')).each($.proxy(function(i,s)
				{
					this.paragraphize.z++;
					this.paragraphize.safes[this.paragraphize.z] = s.outerHTML;
					
					return $(s).replaceWith('\n#####replace' + this.paragraphize.z + '#####\n\n');
					
					
				}, this));
				
				
				return $div.html();
			}).bind(this)
		}
	};
};
