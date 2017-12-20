$.Redactor.prototype.WoltLabList = function() {
	"use strict";
	
	return {
		init: function () {
			var mpCombineAfterAndBefore = this.list.combineAfterAndBefore;
			this.list.combineAfterAndBefore = (function(block) {
				var returnValue = mpCombineAfterAndBefore.call(this, block);
				
				if (returnValue) {
					var list = block.nextElementSibling;
					if ((list.nodeName === 'OL' || list.nodeName === 'UL') && list.childElementCount === 0) {
						elRemove(list);
					}
				}
				
				return returnValue;
			}).bind(this);
		}
	};
};
