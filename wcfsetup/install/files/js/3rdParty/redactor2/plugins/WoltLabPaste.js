$.Redactor.prototype.WoltLabPaste = function() {
	"use strict";
	
	return {
		init: function () {
			var clipboardData = null;
			var isKbd = false;
			
			// IE 11
			var isIe = (document.documentMode && typeof window.clipboardData === 'object');
			
			var mpInit = this.paste.init;
			this.paste.init = (function (e) {
				var isCode = (this.opts.type === 'pre' || this.utils.isCurrentOrParent('pre'));
				isKbd = (!isCode && this.utils.isCurrentOrParent('kbd'));
				if (isCode || isKbd) {
					if (isIe) {
						clipboardData = window.clipboardData.getData('Text');
					}
					else {
						clipboardData = e.originalEvent.clipboardData.getData('text/plain');
					}
					
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
				
				if (isKbd) {
					return clipboardData;
				}
				
				// use clipboard data if paste box is flawed or when
				// pasting in IE 11 where clipboard data is more reliable
				if (pre && (!returnValue || isIe)) {
					return clipboardData;
				}
				
				return returnValue;
			}).bind(this);
			
			// rebind paste event
			this.core.editor().off('paste.redactor').on('paste.redactor', this.paste.init.bind(this));
			
			this.paste.detectClipboardUpload = (function (e) {
				e = e.originalEvent || e;
				
				var file;
				if (isIe) {
					if (!window.clipboardData.files.length) {
						return false;
					}
					
					file = window.clipboardData.files.item(0);
				}
				else if (this.detect.isFirefox()) {
					return false;
				}
				else {
					var clipboard = e.clipboardData;
					
					// prevent safari fake url
					var types = clipboard.types;
					if (Array.isArray(types) && types.indexOf('public.tiff') !== -1) {
						e.preventDefault();
						return false;
					}
					
					if (!clipboard.items || !clipboard.items.length) {
						return;
					}
					
					var cancelPaste = false;
					file = clipboard.items[0].getAsFile();
					if (file === null) {
						if (this.detect.isWebkit() && clipboard.items.length > 1) {
							file = clipboard.items[1].getAsFile();
							cancelPaste = true;
							
							if (file !== null) {
								e.preventDefault();
							}
						}
						
						if (file === null) {
							return false;
						}
					}
				}
				
				var reader = new FileReader();
				reader.readAsDataURL(file);
				reader.onload = this.paste.insertFromClipboard.bind(this);
				
				return (cancelPaste === false);
			}).bind(this);
			
			this.paste.insertFromClipboard = (function (e) {
				if (!window.FormData) {
					return;
				}
				
				this.buffer.set();
				
				WCF.System.Event.fireEvent('com.woltlab.wcf.redactor2', 'pasteFromClipboard_' + this.$element[0].id, {
					blob: this.utils.dataURItoBlob(e.target.result)
				});
				
				this.rtePaste = false;
			}).bind(this);
			
			var transparentGif = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
			var mpInsert = this.paste.insert;
			this.paste.insert = (function(html, data) {
				if (isKbd) data.pre = true;
				
				if (this.utils.isCurrentOrParent('kbd')) {
					mpInsert.call(this, html, data);
					
					var current = this.selection.current();
					if (current.nodeType === Node.TEXT_NODE) current = current.parentNode;
					var kbd = current.closest('kbd');
					var paragraphs = elByTag('p', kbd);
					while (paragraphs.length) {
						paragraphs[0].outerHTML = paragraphs[0].innerHTML;
					}
					
					var parts = kbd.innerHTML.split(/<br\s*\/?>/);
					if (parts.length > 1) {
						var lastParent = this.selection.block();
						
						for (var i = 1, length = parts.length; i < length; i++) {
							var newParent = elCreate(lastParent.nodeName);
							newParent.innerHTML = '<kbd>' + parts[i] + (i === length - 1 ? this.marker.html() : '') + '</kbd>';
							lastParent.parentNode.insertBefore(newParent, lastParent.nextSibling);
							
							lastParent = newParent;
						}
						
						kbd.innerHTML = parts[0];
						
						this.selection.restore();
						
					}
					
					return;
				}
				else if (data.pre) {
					return mpInsert.call(this, html, data);
				}
				
				var div = elCreate('div');
				div.innerHTML = html;
				
				var pastedImages = [];
				if (!data.pre && !data.text) {
					elBySelAll('img', div, (function(img) {
						var src = img.src;
						if (src.indexOf('data:image') === 0 && src !== transparentGif) {
							img.src = transparentGif;
							
							var uuid = WCF.getUUID();
							elData(img, 'uuid', uuid);
							pastedImages.push({
								src: src,
								uuid: uuid
							});
							
							elHide(img);
						}
					}).bind(this));
				}
				
				// fix selection marker
				elBySelAll('.redactor-selection-marker', div, elRemove);
				div.appendChild(elCreate('woltlab-selection-marker'));
				
				mpInsert.call(this, div.innerHTML, data);
				
				var marker = elBySel('woltlab-selection-marker', this.$editor[0]);
				if (marker) {
					var range = document.createRange();
					range.setStartBefore(marker);
					range.setEndBefore(marker);
					
					var selection = window.getSelection();
					selection.removeAllRanges();
					selection.addRange(range);
					
					elRemove(marker);
				}
				
				if (pastedImages.length) {
					window.setTimeout((function () {
						var imgData, img;
						for (var i = 0, length = pastedImages.length; i < length; i++) {
							imgData = pastedImages[i];
							img = elBySel('img[data-uuid="' + imgData.uuid + '"]', this.$editor[0]);
							
							if (img) {
								if (isIe) {
									// Internet Explorer 11 triggers both the event *and* insert the image
									img.parentNode.removeChild(img);
								}
								else {
									WCF.System.Event.fireEvent('com.woltlab.wcf.redactor2', 'pasteFromClipboard_' + this.$element[0].id, {
										blob: this.utils.dataURItoBlob(imgData.src),
										replace: img
									});
								}
							}
						}
					}).bind(this), 50);
				}
				
				this.rtePaste = false;
			}).bind(this);
			
			this.paste.clipboardUpload = function () { /* not required, images are handled in `paste.insert()` below */ };
		}
	};
};
