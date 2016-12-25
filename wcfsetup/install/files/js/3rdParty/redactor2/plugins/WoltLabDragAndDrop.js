$.Redactor.prototype.WoltLabDragAndDrop = function() {
	"use strict";
	
	return {
		init: function() {
			if (!this.opts.woltlab.attachments) {
				return;
			}
			
			require(['WoltLabSuite/Core/Ui/Redactor/DragAndDrop'], (function (UiRedactorDragAndDrop) {
				UiRedactorDragAndDrop.init(this);
			}).bind(this));
		}
	};
};
