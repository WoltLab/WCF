$.Redactor.prototype.WoltLabClean = function() {
	"use strict";
	
	return {
		init: function () {
			this.opts.pasteInlineTags = this.opts.pasteInlineTags.filter(function (value) {
				return (value !== 'span');
			});
			
			var mpOnSet = this.clean.onSet;
			this.clean.onSet = (function (html) {
				return mpOnSet.call(this, html.replace(/\u200B/g, ''));
			}).bind(this);
			
			var mpOnSync = this.clean.onSync;
			this.clean.onSync = (function (html) {
				var div = elCreate('div');
				var replacements = {};
				
				if (html.indexOf('<pre') !== -1) {
					div.innerHTML = html;
					
					elBySelAll('pre', div, function (pre) {
						var uuid = WCF.getUUID();
						
						replacements[uuid] = pre.textContent;
						pre.textContent = uuid;
					});
					
					html = div.innerHTML;
				}
				
				html = html.replace(/<p>\u200B<\/p>/g, '<p><br></p>');
				
				html = mpOnSync.call(this, html);
				
				div.innerHTML = html;
				
				elBySelAll('span', div, function (span) {
					span.outerHTML = span.innerHTML;
				});
				elBySelAll('pre', div, function (pre) {
					if (replacements.hasOwnProperty(pre.textContent)) {
						pre.textContent = replacements[pre.textContent];
					}
				});
				
				html = div.innerHTML;
				
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
			
			var mpStripTags = this.clean.stripTags;
			this.clean.stripTags = (function(input, denied) {
				if (Array.isArray(denied)) {
					denied.push('span');
				}
				
				return mpStripTags.call(this, input, denied);
			}).bind(this);
		}
	}
};
