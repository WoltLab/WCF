$.Redactor.prototype.WoltLabDragAndDrop = function() {
	"use strict";
	
	return {
		init: function() {
			require(['WoltLabSuite/Core/Ui/Redactor/DragAndDrop'], (function (UiRedactorDragAndDrop) {
				UiRedactorDragAndDrop.init(this);
			}).bind(this));
		}
	};
};
