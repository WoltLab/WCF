$.Redactor.prototype.WoltLabInlineCode = function() {
	"use strict";
	
	return {
		init: function() {
			this.opts.activeButtonsStates.kbd = 'tt';
			
			require(['EventHandler'], (function (EventHandler) {
				EventHandler.add('com.woltlab.wcf.redactor2', 'bbcode_tt_' + this.$element[0].id, (function(data) {
					data.cancel = true;
					
					this.button.toggle({}, 'kbd', 'func', 'inline.format');
				}).bind(this));
			}).bind(this));
		}
	};
};
