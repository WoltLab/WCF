$.Redactor.prototype.WoltLabCaret = function() {
	"use strict";
	
	return {
		init: function () {
			this.list.combineAfterAndBefore = (function (block) {
				var $prev = $(block).prev();
				var $next = $(block).next();
				var isEmptyBlock = (block && block.tagName === 'P' && (block.innerHTML === '<br>' || block.innerHTML === ''));
				
				// WoltLab fix: closest() was missing the 2nd parameter causing
				// it to match on lists being Redactor's ancestor
				var isBlockWrapped = ($prev.closest('ol, ul', this.core.editor()[0]).length === 1 && $next.closest('ol, ul', this.core.editor()[0]).length === 1);
				
				if (isEmptyBlock && isBlockWrapped)
				{
					$prev.children('li').last().append(this.marker.get());
					$prev.append($next.contents());
					this.selection.restore();
					
					return true;
				}
				
				return false;
			}).bind(this);
		}
	};
};
