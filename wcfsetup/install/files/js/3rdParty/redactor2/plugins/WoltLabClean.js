$.Redactor.prototype.WoltLabClean = function() {
	"use strict";
	
	return {
		init: function () {
			var mpOnSet = this.clean.onSet;
			this.clean.onSet = (function (html) {
				return mpOnSet.call(this, html.replace(/\u200B/g, ''));
			}).bind(this);
			
			var mpOnSync = this.clean.onSync;
			this.clean.onSync = (function (html) {
				var div, replacements = {};
				if (html.indexOf('<pre') !== -1) {
					div = elCreate('div');
					div.innerHTML = html;
					
					elBySelAll('pre', div, function (pre) {
						var uuid = WCF.getUUID();
						
						replacements[uuid] = pre.textContent;
						pre.textContent = uuid;
					});
					
					html = div.innerHTML;
				}
				
				html = mpOnSync.call(this, html);
				
				if (div) {
					div.innerHTML = html;
					
					elBySelAll('pre', div, function (pre) {
						pre.textContent = replacements[pre.textContent];
					});
					
					html = div.innerHTML;
				}
				
				return html;
			}).bind(this);
			
			var mpSavePreFormatting = this.clean.savePreFormatting;
			this.clean.savePreFormatting = (function (html) {
				var mpCleanEncodeEntities = this.clean.encodeEntities;
				this.clean.encodeEntities = function(str) {
					return WCF.String.escapeHTML(str);
				};
				
				html = mpSavePreFormatting.call(this, html);
				
				// revert to original method
				this.clean.encodeEntities = mpCleanEncodeEntities;
				
				return html;
			}).bind(this);
		}
	}
};
