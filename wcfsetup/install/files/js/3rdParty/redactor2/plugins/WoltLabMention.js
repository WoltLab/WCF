$.Redactor.prototype.WoltLabMention = function() {
	"use strict";
	
	return {
		init: function() {
			//var WoltLabMention = document.registerElement('woltlab-mention');
			
			require(['WoltLabSuite/Core/Ui/Redactor/Mention'], (function(UiRedactorMention) {
				new UiRedactorMention(this);
			}).bind(this));
		}
	};
};
