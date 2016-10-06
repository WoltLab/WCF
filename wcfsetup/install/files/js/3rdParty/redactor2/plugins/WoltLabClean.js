$.Redactor.prototype.WoltLabClean = function() {
	"use strict";
	
	return {
		init: function () {
			this.opts.pasteInlineTags = this.opts.pasteInlineTags.filter(function (value) {
				return (value !== 'span');
			});
			
			var mpOnSet = this.clean.onSet;
			this.clean.onSet = (function (html) {
				html = html.replace(/\u200B/g, '');
				
				// fix ampersands being replaced
				html = html.replace(/&amp;/g, '@@@WCF_AMPERSAND@@@');
				
				html = mpOnSet.call(this, html);
				
				// restore ampersands
				html = html.replace(/@@@WCF_AMPERSAND@@@/g, '&amp;');
				
				// remove iframes smuggled into the HTML by the user
				// they're removed on the server anyway, but keeping
				// them in the wysiwyg may lead to false impressions
				var div = elCreate('div');
				div.innerHTML = html;
				elBySelAll('iframe', div, elRemove);
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
						if (br.nextSibling && br.nextSibling.textContent.match(/^[\s\u200B]+$/)) {
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
			
			var mpOnPaste = this.clean.onPaste;
			this.clean.onPaste = (function (html, data, insert) {
				if (data.pre) {
					return mpOnPaste.call(this, html, data, insert);
				}
				
				var div = elCreate('div');
				div.innerHTML = html;
				
				var element, elements = elBySelAll('[style]', div), property, removeStyles;
				for (var i = 0, length = elements.length; i < length; i++) {
					element = elements[i];
					
					removeStyles = [];
					for (var j = 0, innerLength = element.style.length; j < innerLength; j++) {
						property = element.style[j];
						
						if (this.WoltLabClean._applyInlineStyle(element, property, element.style.getPropertyValue(property))) {
							removeStyles.push(property);
						}
					}
					
					removeStyles.forEach(function (property) {
						element.style.removeProperty(property);
					});
				}
				
				return mpOnPaste.call(this, div.innerHTML, data, insert);
			}).bind(this);
			
			var storage = [];
			var addToStorage = function (element, attributes) {
				var attrs = {};
				for (var i = 0, length = attributes.length; i < length; i++) {
					attrs[attributes[i]] = elAttr(element, attributes[i]);
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
				
				storage.forEach(function (item, i) {
					item.element.outerHTML = '###custom' + i + '###' + item.element.innerHTML + '###/custom' + i + '###';
				});
				
				return mpConvertTags.call(this, div.innerHTML, data);
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
		},
		
		_applyInlineStyle: function (element, property, value) {
			var className = '', tagName = '';
			
			switch (property) {
				case 'font-weight':
					if (value == 600) {
						if (element.closest('strong') === null) {
							tagName = 'strong';
						}
					}
					break;
			}
			
			if (tagName) {
				var newElement = elCreate(tagName);
				if (className) newElement.className = className;
				
				element.parentNode.insertBefore(newElement, element);
				newElement.appendChild(element);
				
				return true;
			}
			
			return false;
		}
	}
};
