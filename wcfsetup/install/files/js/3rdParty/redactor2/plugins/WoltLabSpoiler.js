$.Redactor.prototype.WoltLabSpoiler = function() {
	"use strict";
	
	return {
		init: function() {
			// register custom block element
			this.WoltLabBlock.register('woltlab-spoiler', true);
			
			// support for active button marking
			this.opts.activeButtonsStates['woltlab-spoiler'] = 'woltlabSpoiler';
			
			require(['WoltLabSuite/Core/Ui/Redactor/Spoiler'], (function (UiRedactorSpoiler) {
				new UiRedactorSpoiler(this);
			}).bind(this));
		}
	};
};
