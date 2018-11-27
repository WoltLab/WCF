$.Redactor.prototype.WoltLabArticle = function() {
	"use strict";
	
	return {
		init: function() {
			var button = this.button.add('woltlabArticle', '');
			
			require(['WoltLabSuite/Core/Ui/Redactor/Article'], (function (UiRedactorArticle) {
				new UiRedactorArticle(this, button[0]);
			}).bind(this));
		}
	};
};
