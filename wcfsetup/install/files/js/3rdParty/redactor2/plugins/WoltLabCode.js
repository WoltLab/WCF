$.Redactor.prototype.WoltLabCode = function() {
	"use strict";
	
	return {
		init: function() {
			require(['WoltLabSuite/Core/Ui/Redactor/Code'], (function (UiRedactorCode) {
				new UiRedactorCode(this);
			}).bind(this));
			
			var mpStart = this.code.start;
			this.code.start = (function (html) {
				mpStart.call(this, html);
				
				WCF.System.Event.fireEvent('com.woltlab.wcf.redactor2', 'codeStart_' + this.$element[0].id);
				
				// force insert zero-width white space after each <kbd>
				elBySelAll('kbd', this.$editor[0], function (kbd) {
					var nextSibling = kbd.nextSibling;
					if (nextSibling && nextSibling.nodeType === Node.TEXT_NODE && nextSibling.textContent.substr(0, 1) === "\u200B") {
						return;
					}
					
					var textNode = document.createTextNode("\u200B");
					kbd.parentNode.insertBefore(textNode, nextSibling);
				});
			}).bind(this);
			
			var mpSet = this.code.set;
			this.code.set = (function(html, options) {
				mpSet.call(this, html, options);
				
				if (this.utils.isEmpty()) {
					// reset all buttons
					this.observe.toolbar();
				}
			}).bind(this);
			
			var mpGet = this.code.get;
			this.code.get = (function () {
				// force sync
				this.code.html = false;
				this.code.startSync(this.core.editor().html());
				
				return mpGet.call(this);
			}).bind(this);
		}
	};
};
