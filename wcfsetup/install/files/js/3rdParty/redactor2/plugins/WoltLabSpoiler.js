$.Redactor.prototype.WoltLabSpoiler = function() {
	"use strict";
	
	return {
		init: function() {
			require(['WoltLab/WCF/Ui/Redactor/Spoiler'], (function (UiRedactorSpoiler) {
				new UiRedactorSpoiler(this);
			}).bind(this));
		}
	};
};
