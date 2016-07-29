$.Redactor.prototype.WoltLabAutosave = function() {
	"use strict";
	
	return {
		init: function () {
			//noinspection JSUnresolvedVariable
			if (this.opts.woltlab.autosave) {
				//noinspection JSUnresolvedVariable
				this.opts.woltlab.autosave.watch(this);
				
				WCF.System.Event.addListener('com.woltlab.wcf.redactor2', 'autosaveDestroy_' + this.$element[0].id, this.WoltLabAutosave.destroy.bind(this));
				WCF.System.Event.addListener('com.woltlab.wcf.redactor2', 'autosaveReset_' + this.$element[0].id, this.WoltLabAutosave.reset.bind(this));
			}
		},
		
		destroy: function () {
			//noinspection JSUnresolvedVariable
			if (this.opts.woltlab.autosave) {
				//noinspection JSUnresolvedVariable
				this.opts.woltlab.autosave.destroy();
			}
		},
		
		reset: function () {
			//noinspection JSUnresolvedVariable
			if (this.opts.woltlab.autosave) {
				//noinspection JSUnresolvedVariable
				this.opts.woltlab.autosave.clear();
			}
		}
	};
};
