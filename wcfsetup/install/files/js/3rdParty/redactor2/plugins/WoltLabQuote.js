$.Redactor.prototype.WoltLabQuote = function() {
	"use strict";
	
	return {
		init: function() {
			var button = this.button.add('woltlabQuote', '');
			
			this.WoltLabBlock.register('woltlab-quote', true);
			this.opts.replaceTags.blockquote = 'woltlab-quote';
			
			// support for active button marking
			this.opts.activeButtonsStates['woltlab-quote'] = 'woltlabQuote';
			
			require(['WoltLabSuite/Core/Ui/Redactor/Quote'], (function (UiRedactorQuote) {
				new UiRedactorQuote(this, button);
			}).bind(this));
		}
	};
};
