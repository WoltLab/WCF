$.Redactor.prototype.WoltLabEvent = function() {
	"use strict";
	
	return {
		init: function() {
			require(['EventHandler'], this.WoltLabEvent._setEvents.bind(this));
		},
		
		_setEvents: function(EventHandler) {
			var elementId = this.$element[0].id;
			
			var observeLoad = this.observe.load;
			this.observe.load = (function() {
				observeLoad.call(this);
				
				EventHandler.fire('com.woltlab.wcf.redactor', 'observe_load_' + elementId, {
					editor: this.$editor[0]
				});
			}).bind(this);
		}
	};
};
