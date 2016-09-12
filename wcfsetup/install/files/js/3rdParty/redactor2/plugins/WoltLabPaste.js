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
			
			this.paste.detectClipboardUpload = (function (e) {
				e = e.originalEvent || e;
				
				var clipboard = e.clipboardData;
				
				// WoltLab modification: allow Edge
				if (this.detect.isIe() && (this.detect.isIe() !== 'edge' || document.documentMode))
				{
					return true;
				}
				
				if (this.detect.isFirefox())
				{
					return false;
				}
				
				// prevent safari fake url
				var types = clipboard.types;
				// WoltLab modification: `DataTransfer.types` is a `DOMStringList` in Edge
				if (Array.isArray(types) && types.indexOf('public.tiff') !== -1)
				{
					e.preventDefault();
					return false;
				}
				
				if (!clipboard.items || !clipboard.items.length)
				{
					return;
				}
				
				var isWebkitPaste = false;
				var file = clipboard.items[0].getAsFile();
				if (file === null)
				{
					if (this.detect.isWebkit() && clipboard.items.length > 1) {
						file = clipboard.items[1].getAsFile();
						isWebkitPaste = true;
					}
					
					if (file === null) {
						return false;
					}
				}
				
				var reader = new FileReader();
				reader.readAsDataURL(file);
				reader.onload = this.paste.insertFromClipboard.bind(this);
				
				return (isWebkitPaste === false);
			}).bind(this);
			
			this.paste.insertFromClipboard = (function (e) {
				if (!window.FormData) {
					return;
				}
				
				this.buffer.set();
				
				WCF.System.Event.fireEvent('com.woltlab.wcf.redactor2', 'pasteFromClipboard_' + this.$element[0].id, {
					blob: this.utils.dataURItoBlob(e.target.result)
				});
			}).bind(this);
			
			this.paste.clipboardUpload = (function () {
				elBySelAll('img', this.$editor[0], (function (img) {
					if (!window.FormData || img.src.indexOf('data:image') !== 0) {
						return;
					}
					
					this.buffer.set();
					
					elHide(img);
					
					WCF.System.Event.fireEvent('com.woltlab.wcf.redactor2', 'pasteFromClipboard_' + this.$element[0].id, {
						blob: this.utils.dataURItoBlob(img.src),
						replace: img
					});
				}).bind(this));
			}).bind(this);
			
			var mpInsert = this.paste.insert;
			this.paste.insert = (function(html, data) {
				if (!data.pre && !data.text) {
					var div = elCreate('div');
					div.innerHTML = html;
					
					elBySelAll('img', this.$editor[0], function (img) {
						if (img.src.indexOf('data:image') === 0) {
							elHide(img);
						}
					});
					
					html = div.innerHTML;
				}
				
				mpInsert.call(this, html, data);
			}).bind(this);
		}
	};
};
