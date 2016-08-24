$.Redactor.prototype.WoltLabPaste = function() {
	"use strict";
	
	return {
		init: function () {
			var clipboardData = null;
			
			var mpInit = this.paste.init;
			this.paste.init = (function (e) {
				var isCode = (this.opts.type === 'pre' || this.utils.isCurrentOrParent('pre')) ? true : false;
				if (isCode) {
					clipboardData = e.originalEvent.clipboardData.getData('text/plain');
					
					var mpCleanEncodeEntities = this.clean.encodeEntities;
					this.clean.encodeEntities = (function(str) {
						// revert to original method
						this.clean.encodeEntities = mpCleanEncodeEntities;
						
						return WCF.String.escapeHTML(str);
					}).bind(this);
				}
				
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
