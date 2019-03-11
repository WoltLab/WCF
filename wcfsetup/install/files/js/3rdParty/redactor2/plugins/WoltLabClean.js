$.Redactor.prototype.WoltLabClean = function() {
	"use strict";
	
	return {
		init: function () {
			var mpOnSet = this.clean.onSet;
			this.clean.onSet = (function (html) {
				html = html.replace(/\u200B/g, '');
				
				// fix ampersands being replaced
				//html = html.replace(/&amp;/g, '@@@WCF_AMPERSAND@@@');
				html = html.replace(/&amp;amp;/g, '@@@WCF_LITERAL_AMP@@@');
				html = html.replace(/&amp;/g, '&amp;WCF_AMPERSAND&amp;');
				
				html = mpOnSet.call(this, html);
				
				// restore ampersands
				//html = html.replace(/@@@WCF_AMPERSAND@@@/g, '&amp;');
				html = html.replace(/&amp;WCF_AMPERSAND&(amp;)?/g, '&amp;');
				html = html.replace(/@@@WCF_LITERAL_AMP@@@/, '&amp;amp;');
				
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
				
				// each `<td>` must at least contain \u200B, otherwise
				// Firefox will be unable to place the caret inside
				elBySelAll('td', div, function (td) {
					if (td.childNodes.length === 0) {
						td.innerHTML = '\u200B';
					}
				});
				
				// enforce at least a single whitespace inside certain block elements
				elBySelAll('pre, woltlab-quote, woltlab-spoiler', div, function (element) {
					if (element.childElementCount === 0 && (element.textContent.length === 0 || element.textContent.match(/^\r?\n$/))) {
						element.textContent = '\u200B';
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
						// Check if there is only whitespace afterwards.
						if (br.nextSibling) {
							if (br.nextSibling.textContent.replace(/[\r\n\t]/g, '').match(/^\u200B+$/)) {
								var newP = elCreate('p');
								newP.innerHTML = '<br>';
								p.parentNode.insertBefore(newP, p.nextSibling);
								
								p.removeChild(br.nextSibling);
								p.removeChild(br);
							}
						}
						else if (br.previousElementSibling || (br.previousSibling && br.previousSibling.textContent.replace(/\u200B/g, '').trim() !== '')) {
							// Firefox inserts bogus `<br>` at the end of paragraphs.
							// See https://bugzilla.mozilla.org/show_bug.cgi?id=656626
							p.removeChild(br);
						}
					}
				});
				
				// Firefox inserts bogus linebreaks instead of spaces at the end of spans, if there is an adjacent span.
				elBySelAll('span', div, function (span) {
					if (span.childNodes.length > 0) {
						var lastNode = span.childNodes[span.childNodes.length - 1];
						if (lastNode.nodeType === Node.TEXT_NODE && lastNode.textContent.match(/\n$/)) {
							lastNode.textContent = lastNode.textContent.replace(/\n+$/, (span.parentNode.lastChild === span ? '' : ' '));
						}
					}
				});
				
				html = div.innerHTML;
				
				html = html.replace(/<p>\u200B<\/p>/g, '<p><br></p>');
				
				// fix ampersands being replaced
				//html = html.replace(/&amp;/g, '@@@WCF_AMPERSAND@@@');
				html = html.replace(/&amp;/g, '&amp;WCF_AMPERSAND&amp;');
				
				html = mpOnSync.call(this, html);
				
				// restore ampersands
				//html = html.replace(/@@@WCF_AMPERSAND@@@/g, '&amp;');
				html = html.replace(/&WCF_AMPERSAND&/g, '&amp;');
				
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
				if (data.pre || this.utils.isCurrentOrParent('kbd')) {
					// instead of calling the original method, we'll use a subset of the cleaning
					// tasks in order to avoid malformed HTML being sanitized by Redactor
					if (data.pre && this.opts.preSpaces) {
						html = html.replace(/\t/g, new Array(this.opts.preSpaces + 1).join(' '));
					}
					
					return WCF.String.escapeHTML(html);
				}
				
				var div = elCreate('div');
				div.innerHTML = html.replace(/@@@WOLTLAB-P-ALIGN-(?:left|right|center|justify)@@@/g, '');
				
				// handle text that appears to be pasted from a text-only editor or <textarea>
				var element, i, length;
				var isRawText = true;
				for (i = 0, length = div.childElementCount; i < length; i++) {
					element = div.children[i];
					
					if (element.nodeName !== 'DIV' || element.childNodes.length === 0) {
						isRawText = false;
						break;
					}
					
					if (element.childNodes.length === 1 && element.childElementCount === 1) {
						var child = element.children[0];
						if (child.childNodes.length === 0 && child.nodeName !== 'BR') {
							isRawText = false;
							break;
						}
					}
				}
				
				if (isRawText) {
					var divs = [];
					for (i = 0, length = div.childElementCount; i < length; i++) {
						divs.push(div.children[i]);
					}
					
					divs.forEach(function (element) {
						var p = elCreate('p');
						div.insertBefore(p, element);
						while (element.childNodes.length > 0) {
							p.appendChild(element.childNodes[0]);
						}
						
						div.removeChild(element);
					});
				}
				
				var isOffice = (elBySel('.MsoNormal', div) !== null);
				
				var elements = elBySelAll('[style]', div), property, removeStyles, strong, styleValue;
				for (i = 0, length = elements.length; i < length; i++) {
					element = elements[i];
					
					removeStyles = [];
					for (var j = 0, innerLength = element.style.length; j < innerLength; j++) {
						property = element.style[j];
						
						//noinspection JSUnresolvedVariable
						if (this.opts.woltlab.allowedInlineStyles.indexOf(property) === -1) {
							if (property === 'font-weight' && element.nodeName !== 'STRONG') {
								styleValue = element.style.getPropertyValue(property);
								if (styleValue === 'bold' || styleValue === 'bolder') {
									styleValue = 600;
								}
								
								styleValue = ~~styleValue;
								if (styleValue > 500) {
									// treat anything above 500 as bold
									strong = elCreate('strong');
									element.parentNode.insertBefore(strong, element);
									strong.appendChild(element);
								}
							}
							else if (isOffice && property === 'margin-bottom' && element.nodeName === 'P') {
								// office sometimes uses a margin-bottom value of exactly 12pt to create spacing between paragraphs
								styleValue = element.style.getPropertyValue(property);
								if (styleValue.match(/^12(?:\.0)?pt$/)) {
									var p = elCreate('p');
									p.innerHTML = '<br>';
									element.parentNode.insertBefore(p, element.nextSibling);
								}
							}
							
							removeStyles.push(property);
						}
					}
					
					removeStyles.forEach(function (property) {
						element.style.removeProperty(property);
					});
				}
				
				elBySelAll('span', div, function (span) {
					if (span.classList.contains('redactor-selection-marker')) return;
					
					if (span.hasAttribute('style') && span.style.length) {
						// Split the styles into separate chunks.
						var color = span.style.getPropertyValue('color');
						var fontFamily = span.style.getPropertyValue('font-family');
						var fontSize = span.style.getPropertyValue('font-size');
						
						var activeStyles = (color ? 1 : 0) + (fontFamily ? 1 : 0) + (fontSize ? 1 : 0);
						while (activeStyles > 1) {
							var newSpan = elCreate('span');
							if (color) {
								newSpan.style.setProperty('color', color, '');
								span.style.removeProperty('color');
								color = '';
								activeStyles--;
							}
							else if (fontFamily) {
								newSpan.style.setProperty('font-family', fontFamily, '');
								span.style.removeProperty('font-family');
								fontFamily = '';
								activeStyles--;
							}
							else if (fontSize) {
								newSpan.style.setProperty('font-size', fontSize, '');
								span.style.removeProperty('font-size');
								fontSize = '';
								activeStyles--;
							}
							
							span.parentNode.insertBefore(newSpan, span);
							newSpan.appendChild(span);
						}
					}
					else {
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
				});
				
				elBySelAll('br', div, function (br) {
					br.parentNode.insertBefore(document.createTextNode('@@@WOLTLAB-BR-MARKER@@@'), br.nextSibling);
				});
				
				// convert `<kbd>…</kbd>` to `[tt]…[/tt]`
				elBySelAll('kbd', div, function(kbd) {
					kbd.insertBefore(document.createTextNode('[tt]'), kbd.firstChild);
					kbd.appendChild(document.createTextNode('[/tt]'));
					
					while (kbd.childNodes.length) {
						kbd.parentNode.insertBefore(kbd.childNodes[0], kbd);
					}
					elRemove(kbd);
				});
				
				html = mpOnPaste.call(this, div.innerHTML, data, insert);
				
				html = html.replace(/\n*@@@WOLTLAB-BR-MARKER@@@\n*/g, '<woltlab-br-marker></woltlab-br-marker>');
				html = html.replace(/(<p>)?\s*@@@WOLTLAB-P-ALIGN-(left|right|center|justify)@@@/g, function (match, p, alignment) {
					if (p) {
						return '<p class="text-' + alignment + '">';
					}
					
					return '';
				});
				
				div.innerHTML = html.replace(/&amp;quot;/g, '&quot;');
				
				elBySelAll('woltlab-br-marker', div, function (marker) {
					var parent = marker.parentNode;
					if (parent === null) {
						// marker was already removed, ignore it
						return;
					}
					
					if (parent.nodeName === 'P') {
						var p = elCreate('p');
						p.innerHTML = '<br>';
						
						var isDoubleBr = false;
						var sibling = marker.nextSibling;
						if (sibling && sibling.nodeName === 'WOLTLAB-BR-MARKER') {
							// equals <br><br> and should be converted into an empty line, splitting the ancestors
							isDoubleBr = true;
						}
						
						var emptySiblings = !isDoubleBr;
						while (marker.nextSibling) {
							if (emptySiblings && marker.nextSibling.textContent.replace(/\u200B/g, '').trim().length !== 0) {
								emptySiblings = false;
							}
							
							p.appendChild(marker.nextSibling);
						}
						
						if (!emptySiblings) {
							// the <br> is not required when there is text afterwards, or if this is a <br><br> match
							elRemove(p.firstElementChild);
						}
						
						var previous = marker.previousSibling;
						if (previous && previous.nodeName === 'BR') {
							elRemove(previous);
						}
						
						parent.parentNode.insertBefore(p, parent.nextSibling);
						
						if (isDoubleBr) {
							p = elCreate('p');
							p.innerHTML = '<br>';
							parent.parentNode.insertBefore(p, parent.nextSibling);
						}
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
					else if (p.textContent.trim().length === 0) {
						elBySelAll('span', p, function (span) {
							if (!span.hasAttribute('style') || !span.style.length) {
								while (span.childNodes.length) {
									span.parentNode.insertBefore(span.childNodes[0], span);
								}
								
								elRemove(span);
							}
						});
						
						if (p.children.length === 0) {
							// fix <p>&nbsp;</p> pasted from Office
							p.innerHTML = '<br>';
						}
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
							link.outerHTML = '#####[a href="' + link.href + '"]#####' + link.innerHTML + '#####[/a]#####';
						}
					});
					
					hadLinks = true;
					data.links = false;
				}
				
				var hadImages = false;
				if (data.images && this.opts.pasteImages) {
					elBySelAll('img', div, function(image) {
						if (image.src) {
							var tmp = '#####[img src="' + image.src + '"';
							var attr;
							for (var j = 0, length = image.attributes.length; j < length; j++) {
								attr = image.attributes.item(j);
								if (attr.name !== 'src') {
									tmp += ' ' + attr.name + '="' + attr.value + '"';
								}
							}
							
							image.outerHTML = tmp + ']#####';
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
			
			var mpGetCurrentType = this.clean.getCurrentType;
			this.clean.getCurrentType = (function(html, insert) {
				var data = mpGetCurrentType.call(this, html, insert);
				
				if (this.utils.isCurrentOrParent(['kbd'])) {
					data.inline = false;
					data.block = false;
					data.encode = true;
					data.pre = true;
					data.paragraphize = false;
					data.images = false;
					data.links = false;
				}
				
				return data;
			}).bind(this);
			
			var mpRemoveEmptyInlineTags = this.clean.removeEmptyInlineTags;
			this.clean.removeEmptyInlineTags = (function(html) {
				var tags = this.opts.inlineTags;
				var $div = $("<div/>").html($.parseHTML(html, document, true));
				var self = this;
				
				var $spans = $div.find('span');
				var $tags = $div.find(tags.join(','));
				
				// WoltLab modification: Preserve the `style` attribute on `<span>` elements. 
				$tags.filter(':not(span)').removeAttr('style');
				
				$tags.each(function () {
					var tagHtml = $(this).html();
					if (this.attributes.length === 0 && self.utils.isEmpty(tagHtml)) {
						$(this).replaceWith(function () {
							return $(this).contents();
						});
					}
				});
				
				$spans.each(function () {
					var tagHtml = $(this).html();
					if (this.attributes.length === 0) {
						$(this).replaceWith(function () {
							return $(this).contents();
						});
					}
				});
				
				html = $div.html();
				
				// convert php tags
				html = html.replace('<!--?php', '<?php');
				html = html.replace('<!--?', '<?');
				html = html.replace('?-->', '?>');
				
				$div.remove();
				
				return html;
			}).bind(this);
		},
		
		removeRedundantStyles: function () {
			var removeElements = [];
			
			// Remove tags that are always safe to remove.
			var plainTags = [
				'del',
				'em',
				'strong',
				'sub',
				'sup',
				'u'
			];
			elBySelAll(plainTags.join(','), this.$editor[0], function(element) {
				if (elBySel(element.nodeName, element) !== null) {
					removeElements.push(element);
				}
			});
			
			// Search for span[style] that contain styles that actually do nothing, because their set style
			// equals the inherited style from its ancestors.
			elBySelAll('span[style]', this.$editor[0], function(element) {
				['color', 'font-family', 'font-size'].forEach(function(propertyName) {
					var value = element.style.getPropertyValue(propertyName);
					if (value) {
						if (window.getComputedStyle(element.parentNode).getPropertyValue(propertyName) === value) {
							removeElements.push(element);
						}
					}
				});
			});
			
			var parent;
			removeElements.forEach(function(element) {
				parent = element.parentNode;
				while (element.childNodes.length) {
					parent.insertBefore(element.childNodes[0], element);
				}
				parent.removeChild(element);
			});
		}
	}
};
