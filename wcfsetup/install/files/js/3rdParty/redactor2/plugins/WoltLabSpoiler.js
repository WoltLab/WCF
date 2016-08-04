$.Redactor.prototype.WoltLabSpoiler = function() {
	"use strict";
	
	return {
		init: function() {
			require(['WoltLabSuite/Core/Ui/Redactor/Spoiler'], (function (UiRedactorSpoiler) {
				new UiRedactorSpoiler(this);
			}).bind(this));
		}
	};
};
