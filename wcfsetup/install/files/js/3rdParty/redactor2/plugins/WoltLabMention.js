$.Redactor.prototype.WoltLabMention = function() {
	"use strict";
	
	return {
		init: function() {
			require(['WoltLabSuite/Core/Ui/Redactor/Mention'], (function(UiRedactorMention) {
				new UiRedactorMention(this);
			}).bind(this));
		}
	};
};
