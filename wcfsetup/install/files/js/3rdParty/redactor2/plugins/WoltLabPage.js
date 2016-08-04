$.Redactor.prototype.WoltLabPage = function() {
	"use strict";
	
	return {
		init: function() {
			var button = this.button.add('woltlabPage', '');
			
			require(['WoltLabSuite/Core/Ui/Redactor/Page'], (function (UiRedactorPage) {
				new UiRedactorPage(this, button[0]);
			}).bind(this));
		}
	};
};
