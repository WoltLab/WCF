$.Redactor.prototype.WoltLabPage = function() {
	"use strict";
	
	return {
		init: function() {
			var button = this.button.add('woltlabPage', '');
			
			require(['WoltLab/WCF/Ui/Redactor/Page'], (function (UiRedactorPage) {
				new UiRedactorPage(this, button[0]);
			}).bind(this));
		}
	};
};
