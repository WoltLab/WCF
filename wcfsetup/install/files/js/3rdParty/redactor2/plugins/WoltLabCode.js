$.Redactor.prototype.WoltLabCode = function() {
	"use strict";
	
	return {
		init: function() {
			require(['WoltLabSuite/Core/Ui/Redactor/Code'], (function (UiRedactorCode) {
				new UiRedactorCode(this);
			}).bind(this));
		}
	};
};
