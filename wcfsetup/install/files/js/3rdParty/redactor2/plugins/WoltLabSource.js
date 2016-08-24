$.Redactor.prototype.WoltLabSource = function() {
	"use strict";
	
	return {
		init: function () {
			// disable caret position in source mode
			this.source.setCaretOnShow = function () {};
			this.source.setCaretOnHide = function (html) { return html; };
			
			var mpHide = this.source.hide;
			this.source.hide = (function () {
				mpHide.call(this);
				
				setTimeout(this.focus.end.bind(this), 100);
			}).bind(this);
			
			var textarea = this.source.$textarea[0];
			
			var mpShow = this.source.show;
			this.source.show = (function () {
				mpShow.call(this);
				
				textarea.selectionStart = textarea.selectionEnd = textarea.value.length;
			}).bind(this);
		}
	};
};
