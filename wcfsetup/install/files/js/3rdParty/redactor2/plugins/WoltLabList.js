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
			
			this.list.toggle = (function(type) {
				if (this.utils.inBlocks(['table', 'td', 'th', 'tr'])) {
					return;
				}
				
				type = (type === 'orderedlist') ? 'ol' : type;
				type = (type === 'unorderedlist') ? 'ul' : type;
				
				type = type.toLowerCase();
				
				this.buffer.set();
				this.selection.save();
				
				var nodes = this.list._getBlocks();
				var block = this.selection.block();
				
				// WoltLab modification: the selector matches lists outside the editor
				//var $list = $(block).parents('ul, ol').last();
				var $list = $(block).parent().closest('ol, ul', this.core.editor()[0]);
				// WoltLab modification END
				if (nodes.length === 0 && $list.length !== 0) {
					nodes = [$list.get(0)];
				}
				
				nodes = (this.list._isUnformat(type, nodes)) ? this.list._unformat(type, nodes) : this.list._format(type, nodes);
				
				this.selection.restore();
				
				return nodes;
			}).bind(this);
		}
	};
};
