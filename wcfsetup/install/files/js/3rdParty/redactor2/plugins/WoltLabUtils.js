$.Redactor.prototype.WoltLabUtils = function() {
	"use strict";
	
	return {
		init: function() {
			var mpReplaceToTag = this.utils.replaceToTag;
			this.utils.replaceToTag = (function (node, tag) {
				if (tag === 'figure') {
					// prevent <p> wrapping an <img> being replaced
					return node;
				}
				
				return mpReplaceToTag.call(this, node, tag);
			}).bind(this);
		}
	};
};
