$.Redactor.prototype.WoltLabPaste = function() {
	"use strict";
	
	var _environment = null;
	
	return {
		init: function () {
			var clipboardData = null;
			var isKbd = false;
			
			// IE 11
			var isIe = (document.documentMode && typeof window.clipboardData === 'object');
			
			var pastedHtml = null, pastedPlainText = null;
			
			// special `init()` implementation for Chrome on Android which seems to have
			// some serious issues with `setTimeout()` during a `paste` event
			var mpInitChromeOnAndroid = (function (e) {
				this.rtePaste = true;
				var pre = !!(this.opts.type === 'pre' || this.utils.isCurrentOrParent('pre'));
				
				this.utils.saveScroll();
				this.selection.save();
				this.paste.createPasteBox(pre);
				
				var html = this.paste.getPasteBoxCode(pre);
				
				// buffer
				this.buffer.set();
				this.selection.restore();
				
				this.utils.restoreScroll();
				
				// paste info
				var data = this.clean.getCurrentType(html);
				
				// clean
				html = this.clean.onPaste(html, data);
				
				// callback
				var returned = this.core.callback('paste', html);
				html = (typeof returned === 'undefined') ? html : returned;
				
				this.paste.insert(html, data);
				this.rtePaste = false;
				
				// clean pre breaklines
				if (pre) this.clean.cleanPre();
			}).bind(this);
			
			var mpInit = this.paste.init;
			this.paste.init = (function (e) {
				pastedPlainText = pastedHtml = null;
				
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
				else if (!isIe) {
					var types = e.originalEvent.clipboardData.types;
					var hasContent = false;
					if (types.indexOf('text/html') !== -1) {
						// handles all major browsers except iOS Safari which does not expose `text/html`,
						// but instead gives us `public.rtf` (which of course is completely useless)
						// https://bugs.webkit.org/show_bug.cgi?id=19893
						pastedHtml = e.originalEvent.clipboardData.getData('text/html');
						
						// remove document fragments
						if (pastedHtml.trim().match(/^<html[^>]*>[\s\S]*?<body[^>]*>([\s\S]+)<\/body>[\s\S]*?<\/html>$/)) {
							pastedHtml = RegExp.$1.replace(/^\s*(?:<!--StartFragment-->)(.+)(?:<!--EndFragment-->)?\s*$/, '$1');
						}
						
						hasContent = (pastedHtml.trim().length !== 0);
					}
					
					if (!hasContent && types.indexOf('text/plain') !== -1) {
						var tmp = WCF.String.escapeHTML(e.originalEvent.clipboardData.getData('text/plain'));
						
						pastedPlainText = '';
						var lines = tmp.split("\n");
						if (lines.length === 1) {
							// paste single-line content as real plain text
							pastedPlainText = tmp;
						}
						else {
							// plain newline characters do not work out well, mimic HTML instead
							lines.forEach(function (line) {
								line = line.trim();
								if (line === '') line = '<br>';
								
								pastedPlainText += '<p>' + line + '</p>';
							});
						}
					} 
				}
				
				if (pastedPlainText !== null || pastedHtml !== null) {
					e.preventDefault();
				}
				
				if (_environment.platform() === 'android' && _environment.browser() === 'chrome') {
					mpInitChromeOnAndroid(e);
				}
				else {
					mpInit.call(this, e);
				}
			}).bind(this);
			
			require(['Environment'], (function(Environment) {
				_environment = Environment;
				
				if (_environment.platform() === 'ios') {
					var mpAppendPasteBox = this.paste.appendPasteBox;
					this.paste.appendPasteBox = (function() {
						// iOS doesn't like `position: fixed` and font-sizes below 16px that much
						this.$pasteBox.css({
							fontSize: '16px',
							height: '1px',
							left: '1px',
							overflow: 'hidden',
							position: 'absolute',
							top: (~~(window.innerHeight / 4) + window.pageYOffset) + 'px',
							width: '1px'
						});
						
						mpAppendPasteBox.call(this);
					}).bind(this);
				}
			}).bind(this));
			
			var mpCreatePasteBox = this.paste.createPasteBox;
			this.paste.createPasteBox = (function (pre) {
				if (pastedHtml === null && pastedPlainText === null) {
					mpCreatePasteBox.call(this, pre);
				}
				
				// do nothing
			}).bind(this);
			
			var mpGetPasteBoxCode = this.paste.getPasteBoxCode;
			this.paste.getPasteBoxCode = (function (pre) {
				if (pastedHtml !== null || pastedPlainText !== null) {
					// prevent page scrolling
					this.tmpScrollTop = undefined;
					
					return pastedHtml || pastedPlainText;
				}
				
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
				
				var file = null;
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
					
					// Safari
					var types = clipboard.types;
					if (Array.isArray(types) && types.indexOf('public.tiff') !== -1) {
						if (clipboard.files.length === 0) {
							// pasted an `<img>` element from clipboard
							return;
						}
						else if (clipboard.files.length === 1) {
							// This may not work if the file was copied from Finder for whatever
							// reasons and it is not possible to try/catch this error (wow!).
							// 
							// It does not have any side-effects when failing other than canceling
							// out the `paste` event, which is pretty much what we're looking for
							// anyway. It does work from certain apps, but Safari exposes too little
							// information to tell them apart, so we just have to try it.
							// 
							// See https://bugs.webkit.org/show_bug.cgi?id=171504
							file = clipboard.files[0];
							cancelPaste = true;
							
							if (file !== null) {
								e.preventDefault();
							}
						}
						else {
							e.preventDefault();
							return false;
						}
					}
					
					if (file === null) {
						if (!clipboard.items || !clipboard.items.length) {
							return;
						}
						
						if (this.detect.isWebkit()) {
							var item, hasFile = false, hasHtml = false;
							for (var i = 0, length = clipboard.items.length; i < length; i++) {
								item = clipboard.items[i];
								if (item.kind === 'string' && item.type === 'text/html') hasHtml = true;
								else if (item.kind === 'file') hasFile = true;
							}
							
							// pasted an `<img>` element from clipboard
							if (hasFile && hasHtml) {
								return false;
							}
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
						else if (this.detect.isWebkit() && clipboard.items.length === 1) {
							// Newer Chromium based browsers will paste the base64 encoded
							// image along with the provided File object.
							e.preventDefault();
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
				
				// Some browsers implicitly convert links into `<a>` elements.
				if (div.childElementCount === 1 && div.children[0].nodeName === 'A') {
					var link = div.children[0];
					if (div.firstChild === link && div.lastChild === link && link.href === link.textContent) {
						while (link.childNodes.length) {
							div.insertBefore(link.childNodes[0], link);
						}
						
						div.removeChild(link);
					}
				}
				
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
				
				// Convert <br> inside <pre> into plain newline characters.
				elBySelAll('pre', div, function (pre) {
					elBySelAll('br', pre, function (br) {
						var parent = br.parentNode;
						parent.insertBefore(document.createTextNode("\n"), br);
						parent.removeChild(br);
					});
				});
				
				mpInsert.call(this, div.innerHTML, data);
				
				// check if the caret is now inside an <a> element, but at
				// the very last position
				var selection = window.getSelection();
				if (selection.rangeCount && selection.anchorNode.nodeName === 'A' && selection.anchorOffset === selection.anchorNode.childNodes.length) {
					this.caret.after(selection.anchorNode);
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
				
				// cleanup any stray text fragments caused by paragraphs
				// being replaced by the clipboard contents
				var node, badNodes = [];
				var editor = this.core.editor()[0];
				for (i = 0, length = editor.childNodes.length; i < length; i++) {
					node = editor.childNodes[i];
					if (node.nodeType === Node.TEXT_NODE && node.textContent === '\u200B') {
						badNodes.push(node);
					}
				}
				badNodes.forEach(elRemove);
				
				this.WoltLabClean.removeRedundantStyles();
				
				this.rtePaste = false;
			}).bind(this);
			
			this.paste.clipboardUpload = function () { /* not required, images are handled in `paste.insert()` below */ };
		}
	};
};
