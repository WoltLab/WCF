$.Redactor.prototype.WoltLabLine = function() {
	"use strict";
	
	return {
		init: function() {
			this.line.removeOnBackspace = (function () {
				if (!this.utils.isCollapsed())
				{
					return;
				}
				
				var $block = $(this.selection.block());
				if ($block.length === 0 || !this.utils.isStartOfElement($block))
				{
					return;
				}
				
				// if hr is previous element
				var $prev = $block.prev();
				// WoltLab modification: check was for `$prev` (always true) instead of `$prev.length`
				if ($prev.length && $prev[0].tagName === 'HR')
				{
					e.preventDefault();
					$prev.remove();
				}
			}).bind(this);
		}
	};
};
