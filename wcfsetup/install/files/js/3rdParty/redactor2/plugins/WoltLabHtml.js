$.Redactor.prototype.WoltLabHtml = function() {
	"use strict";
	
	return {
		init: function() {
			require(['WoltLabSuite/Core/Ui/Redactor/Html'], (function (UiRedactorHtml) {
				new UiRedactorHtml(this);
			}).bind(this));
		}
	};
};