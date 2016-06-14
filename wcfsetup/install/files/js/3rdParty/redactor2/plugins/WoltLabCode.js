$.Redactor.prototype.WoltLabCode = function() {
	"use strict";
	
	return {
		init: function() {
			require(['WoltLab/WCF/Ui/Redactor/Code'], (function (UiRedactorCode) {
				new UiRedactorCode(this);
			}).bind(this));
		}
	};
};
