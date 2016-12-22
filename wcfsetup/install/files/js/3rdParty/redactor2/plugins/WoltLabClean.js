$.Redactor.prototype.WoltLabClean = function() {
	"use strict";
	
	return {
		init: function () {
			var mpOnSet = this.clean.onSet;
			this.clean.onSet = (function (html) {
				html = html.replace(/\u200B/g, '');
				
				// fix ampersands being replaced
				html = html.replace(/&amp;/g, '@@@WCF_AMPERSAND@@@');
				
				html = mpOnSet.call(this, html);
				
				// restore ampersands
				html = html.replace(/@@@WCF_AMPERSAND@@@/g, '&amp;');
				
				var div = elCreate('div');
				div.innerHTML = html;
				
				// remove iframes smuggled into the HTML by the user
				// they're removed on the server anyway, but keeping
				// them in the wysiwyg may lead to false impressions
				elBySelAll('iframe', div, elRemove);
				
				// strip script tags
				elBySelAll('pre', div, function (pre) {
					if (pre.classList.contains('redactor-script-tag')) {
						elRemove(pre);
					}
				});
				
				html = div.innerHTML;
				
				return html;
			}).bind(this);
			
			var mpOnSync = this.clean.onSync;
			this.clean.onSync = (function (html) {
				var div = elCreate('div');
				div.innerHTML = html;
				var replacements = {};
				
				elBySelAll('pre', div, function (pre) {
					var uuid = WCF.getUUID();
					
					replacements[uuid] = pre.textContent;
					pre.textContent = uuid;
				});
				
				// handle <p> with trailing `<br>\u200B`
				elBySelAll('p', div, function (p) {
					var br = p.lastElementChild;
					if (br && br.nodeName === 'BR') {
						// check if there is only whitespace afterwards
						if (br.nextSibling && br.nextSibling.textContent.replace(/[\r\n\t]/g, '').match(/^\u200B+$/)) {
							var newP = elCreate('p');
							newP.innerHTML = '<br>';
							p.parentNode.insertBefore(newP, p.nextSibling);
							
							p.removeChild(br.nextSibling);
							p.removeChild(br);
						}
					}
				});
				
				html = div.innerHTML;
				
				html = html.replace(/<p>\u200B<\/p>/g, '<p><br></p>');
				
				// fix ampersands being replaced
				html = html.replace(/&amp;/g, '@@@WCF_AMPERSAND@@@');
				
				html = mpOnSync.call(this, html);
				
				// restore ampersands
				html = html.replace(/@@@WCF_AMPERSAND@@@/g, '&amp;');
				
				div.innerHTML = html;
				
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
			
			var mpOnPaste = this.clean.onPaste;
			this.clean.onPaste = (function (html, data, insert) {
				if (data.pre) {
					// prevent method call when data.pre is true
					var mpRemoveEmptyInlineTags = this.clean.removeEmptyInlineTags;
					this.clean.removeEmptyInlineTags = function(html) { return html; };
					
					html = mpOnPaste.call(this, html, data, insert);
					
					this.clean.removeEmptyInlineTags = mpRemoveEmptyInlineTags;
					
					return html;
				}
				
				var div = elCreate('div');
				div.innerHTML = html.replace(/@@@WOLTLAB-P-ALIGN-(?:left|right|center|justify)@@@/g, '');
				
				var element, elements = elBySelAll('[style]', div), property, removeStyles;
				for (var i = 0, length = elements.length; i < length; i++) {
					element = elements[i];
					
					removeStyles = [];
					for (var j = 0, innerLength = element.style.length; j < innerLength; j++) {
						property = element.style[j];
						
						//noinspection JSUnresolvedVariable
						if (this.opts.woltlab.allowedInlineStyles.indexOf(property) === -1) {
							removeStyles.push(property);
						}
					}
					
					removeStyles.forEach(function (property) {
						element.style.removeProperty(property);
					});
				}
				
				elBySelAll('span', div, function (span) {
					if (!span.hasAttribute('style') || !span.style.length) {
						while (span.childNodes.length) {
							span.parentNode.insertBefore(span.childNodes[0], span);
						}
						
						elRemove(span);
					}
				});
				
				elBySelAll('p', div, function (p) {
					if (p.classList.contains('MsoNormal')) {
						// Empty lines in Microsoft Word are represented with <o:p>&nbsp;</o:p>
						if (p.childElementCount === 1 && p.children[0].nodeName === 'O:P' && p.textContent === '\u00A0') {
							p.innerHTML = '<br>';
						}
					}
					else if (p.className.match(/\btext-(left|right|center|justify)\b/)) {
						p.insertBefore(document.createTextNode('@@@WOLTLAB-P-ALIGN-' + RegExp.$1 + '@@@'), p.firstChild);
					}
					
					// discard classes and styles, they're stripped later on anyway
					p.removeAttribute('class');
					p.removeAttribute('style');
				});
				
				elBySelAll('img', div, function (img) {
					img.removeAttribute('style');
					
					if (img.hasAttribute('alt')) {
						// The editor trips over `<`, causing the DOM to be seriously
						// messed up. Until this is resolved, we're simply dropping it,
						// at least for smilies it is later restored.
						img.setAttribute('alt', img.getAttribute('alt').replace(/</g, ''));
					}
				});
				
				elBySelAll('br', div, function (br) {
					br.parentNode.insertBefore(document.createTextNode('@@@WOLTLAB-BR-MARKER@@@'), br.nextSibling);
				});
				
				html = mpOnPaste.call(this, div.innerHTML, data, insert);
				
				html = html.replace(/@@@WOLTLAB-BR-MARKER@@@/g, '<woltlab-br-marker></woltlab-br-marker>');
				html = html.replace(/(<p>)?\s*@@@WOLTLAB-P-ALIGN-(left|right|center|justify)@@@/g, function (match, p, alignment) {
					if (p) {
						return '<p class="text-' + alignment + '">';
					}
					
					return '';
				});
				
				div.innerHTML = html.replace(/&amp;quot;/g, '&quot;');
				
				elBySelAll('woltlab-br-marker', div, function (marker) {
					var parent = marker.parentNode;
					
					if (parent.nodeName === 'P') {
						var p = elCreate('p');
						p.innerHTML = '<br>';
						
						while (marker.nextSibling) {
							p.appendChild(marker.nextSibling);
						}
						
						var previous = marker.previousSibling;
						if (previous && previous.nodeName === 'BR') {
							elRemove(previous);
						}
						
						parent.parentNode.insertBefore(p, parent.nextSibling);
					}
					else {
						parent.insertBefore(elCreate('br'), marker);
					}
					
					elRemove(marker);
				});
				
				elBySelAll('p', div, function (p) {
					// remove garbage paragraphs that contain absolutely nothing
					var remove = false;
					if (p.childNodes.length === 0) {
						remove = true;
					}
					else if (p.textContent === '') {
						remove = true;
						
						// check if there are only <span> elements
						elBySelAll('*', p, function (element) {
							if (element.nodeName !== 'SPAN') {
								remove = false;
							}
						});
					}
					
					if (remove) {
						elRemove(p);
					}
				});
				
				return div.innerHTML;
			}).bind(this);
			
			var storage = [];
			var addToStorage = function (element, attributes) {
				var attr, attrs = {}, value;
				for (var i = 0, length = attributes.length; i < length; i++) {
					attr = attributes[i];
					value = elAttr(element, attr);
					
					// Chrome likes to break the font-family tag by encoding quotes
					// that are then no longer accepted by `style.setPropertyValue()`
					if (attr === 'style' && element.style.length === 0 && value.indexOf('font-family') === 0) {
						value = value.replace(/&quot;/g, '');
					}
					
					attrs[attr] = value;
				}
				
				storage.push({
					element: element,
					attributes: attrs
				});
			};
			
			var mpConvertTags = this.clean.convertTags;
			this.clean.convertTags = (function(html, data) {
				var div = elCreate('div');
				div.innerHTML = html;
				
				// reset tag storage
				storage = [];
				
				WCF.System.Event.fireEvent('com.woltlab.wcf.redactor2', 'convertTags_' + this.$element[0].id, {
					addToStorage: addToStorage,
					div: div
				});
				
				elBySelAll('span', div, function (span) {
					addToStorage(span, ['style']);
				});
				
				storage.forEach(function (item, i) {
					var element = item.element;
					var parent = element.parentNode;
					
					parent.insertBefore(document.createTextNode('###custom' + i + '###'), element);
					parent.insertBefore(document.createTextNode('###/custom' + i + '###'), element.nextSibling);
					
					while (element.childNodes.length) {
						parent.insertBefore(element.childNodes[0], element);
					}
					
					parent.removeChild(element);
				});
				
				var hadLinks = false;
				if (data.links && this.opts.pasteLinks) {
					elBySelAll('a', div, function(link) {
						if (link.href) {
							link.outerHTML = '##%a href="' + link.href + '"%##' + link.innerHTML + '##%/a%##';
						}
					});
					
					hadLinks = true;
					data.links = false;
				}
				
				var hadImages = false;
				if (data.images && this.opts.pasteImages) {
					elBySelAll('img', div, function(image) {
						if (image.src) {
							var tmp = '##%img src="' + image.src + '"';
							var attr;
							for (var j = 0, length = image.attributes.length; j < length; j++) {
								attr = image.attributes.item(j);
								if (attr.name !== 'src') {
									tmp += ' ' + attr.name + '="' + attr.value + '"';
								}
							}
							
							image.outerHTML = tmp + '%##';
						}
					});
					
					hadImages = true;
					data.images = false;
				}
				
				html = mpConvertTags.call(this, div.innerHTML, data);
				
				if (hadImages) data.images = true;
				if (hadLinks) data.links = true;
				
				return html;
			}).bind(this);
			
			var mpReconvertTags = this.clean.reconvertTags;
			this.clean.reconvertTags = (function(html, data) {
				if (storage.length) {
					html = html.replace(/###(\/?)custom(\d+)###/g, '<$1woltlab-custom-tag data-index="$2">');
					
					var div = elCreate('div');
					div.innerHTML = html;
					
					elBySelAll('woltlab-custom-tag', div, function (element) {
						var index = ~~elData(element, 'index');
						
						if (storage[index]) {
							var itemData = storage[index];
							var newElement = elCreate(itemData.element.nodeName);
							for (var property in itemData.attributes) {
								if (itemData.attributes.hasOwnProperty(property)) {
									elAttr(newElement, property, itemData.attributes[property]);
								}
							}
							
							element.parentNode.insertBefore(newElement, element);
							while (element.childNodes.length) {
								newElement.appendChild(element.childNodes[0]);
							}
						}
						
						elRemove(element);
					});
					
					html = div.innerHTML;
				}
				
				return mpReconvertTags.call(this, html, data);
			}).bind(this);
			
			this.clean.removeSpans = function(html) {
				return html;
			};
		}
	}
};
