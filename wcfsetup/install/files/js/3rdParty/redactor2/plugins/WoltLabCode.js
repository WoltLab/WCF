$.Redactor.prototype.WoltLabCode = function() {
	"use strict";
	
	return {
		init: function() {
			// disable caret position in source mode
			this.source.setCaretOnShow = function () {};
			this.source.setCaretOnHide = function (html) { return html; };
			
			require(['WoltLabSuite/Core/Ui/Redactor/Code'], (function (UiRedactorCode) {
				new UiRedactorCode(this);
			}).bind(this));
		}
	};
};
