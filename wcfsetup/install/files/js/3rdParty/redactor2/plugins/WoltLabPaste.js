$.Redactor.prototype.WoltLabPaste = function() {
	"use strict";
	
	return {
		init: function () {
			var clipboardData = null;
			
			var mpInit = this.paste.init;
			this.paste.init = (function (e) {
				clipboardData = e.originalEvent.clipboardData.getData('text/plain');
				
				mpInit.call(this, e);
			}).bind(this);
			
			var mpGetPasteBoxCode = this.paste.getPasteBoxCode;
			this.paste.getPasteBoxCode = (function (pre) {
				var returnValue = mpGetPasteBoxCode.call(this, pre);
				
				if (pre && !returnValue) {
					return clipboardData;
				}
				
				return returnValue;
			}).bind(this);
			
			// rebind paste event
			this.core.editor().off('paste.redactor').on('paste.redactor', this.paste.init.bind(this));
		}
	};
};
