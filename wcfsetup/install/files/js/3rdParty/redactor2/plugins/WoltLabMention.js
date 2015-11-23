$.Redactor.prototype.WoltLabMention = function() {
	"use strict";
	
	return {
		init: function() {
			var WoltLabMention = document.registerElement('woltlab-mention');
			
			require(['WoltLab/WCF/Ui/Redactor/Mention'], (function(UiRedactorMention) {
				new UiRedactorMention(this);
			}).bind(this));
		}
	};
};
