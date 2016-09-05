$.Redactor.prototype.WoltLabSmiley = function() {
	"use strict";
	
	return {
		init: function() {
			require(['EventHandler'], (function(EventHandler) {
				EventHandler.add('com.woltlab.wcf.redactor2', 'insertSmiley_' + this.$element[0].id, this.WoltLabSmiley._insert.bind(this));
			}).bind(this));
		},
		
		_insert: function(data) {
			this.buffer.set();
			
			this.insert.html(data.img.cloneNode().outerHTML);
		}
	}
};
