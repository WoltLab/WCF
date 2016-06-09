$.Redactor.prototype.WoltLabCode = function() {
	"use strict";
	
	return {
		init: function() {
			this.opts.activeButtonsStates.pre = 'code';
			
			require(['EventHandler'], (function (EventHandler) {
				EventHandler.add('com.woltlab.wcf.redactor2', 'bbcode_code_' + this.$element[0].id, (function(data) {
					data.cancel = true;
					
					this.button.toggle({}, 'pre', 'func', 'block.format');
					
					var pre = this.selection.block();
					if (pre && pre.nodeName === 'PRE') {
						if (pre.textContent === '') {
							pre.textContent = '\u200B';
						}
						
						if (elData(pre, 'display-value') === '') {
							elData(pre, 'display-value', 'TODO: source code');
						}
					}
				}).bind(this));
			}).bind(this));
		}
	};
};
