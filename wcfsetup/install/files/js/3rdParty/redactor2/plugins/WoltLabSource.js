$.Redactor.prototype.WoltLabSource = function() {
	"use strict";
	
	return {
		init: function () {
			var id = this.$element[0].id;
			
			var fixQuotes = function(container) {
				// fix empty quotes suffering from a superfluous <p></p>
				elBySelAll('woltlab-quote', container, function(quote) {
					if (quote.childElementCount !== 2 || quote.children[0].nodeName !== 'P' || quote.children[1].nodeName !== 'P') {
						return;
					}
					
					var first = quote.children[0];
					if (first.innerHTML.trim() !== '') {
						return;
					}
					
					var last = quote.children[1];
					if (last.innerHTML.trim() !== '<br>') {
						return;
					}
					
					quote.removeChild(first);
				});
			};
			
			var stripIntermediateCode = function(div) {
				elBySelAll('pre, woltlab-quote, woltlab-spoiler', div, function (element) {
					element.removeAttribute('data-title');
				});
				
				WCF.System.Event.fireEvent('com.woltlab.wcf.redactor2', 'source_stripIntermediateCode_' + id, { div: div });
			};
			
			function stripIcons(div) {
				elBySelAll('.icon, .fa', div, function (element) {
					var classNames = element.className.split(' ');
					classNames = classNames.filter(function (value) {
						if (value === 'fa' || value === 'icon') {
							return false;
						}
						else if (value.match(/^icon\d{2}$/)) {
							return false;
						}
						else if (value.match(/^fa-[a-z\-]+$/)) {
							return false;
						}
						
						return true;
					});
					
					element.className = classNames.join(' ');
					if (element.className.trim() === '' && element.innerHTML === '') {
						elRemove(element);
					}
				});
			}
			
			var mpHide = this.source.hide;
			this.source.hide = (function () {
				// use jQuery to parse, its parser is much more graceful
				var div = $('<div />').html(this.source.$textarea.val());
				stripIcons(div[0]);
				
				this.source.$textarea.val(div[0].innerHTML);
				
				mpHide.call(this);
				
				setTimeout((function() {
					this.focus.end();
					
					fixQuotes(this.core.editor()[0]);
				}).bind(this), 100);
				
				this.placeholder.enable();
			}).bind(this);
			
			var textarea = this.source.$textarea[0];
			
			// move textarea in front of the original textarea
			this.$element[0].parentNode.insertBefore(textarea, this.$element[0]);
			
			var mpShow = this.source.show;
			this.source.show = (function () {
				// fix height
				var height = this.$editor[0].offsetHeight;
				
				// the `code.show()` method trashes successive newlines
				var code = this.code.get();
				
				mpShow.call(this);
				
				this.source.$textarea.val(code.replace(/&nbsp;/g, ' '));
				
				// noinspection JSSuspiciousNameCombination
				textarea.style.setProperty('height', Math.ceil(height) + 'px', '');
				textarea.style.setProperty('display', 'block', '');
				
				var div = elCreate('div');
				div.innerHTML = textarea.value;
				fixQuotes(div);
				stripIntermediateCode(div);
				
				textarea.value = this.WoltLabSource.format(div.innerHTML);
				
				textarea.selectionStart = textarea.selectionEnd = textarea.value.length;
			}).bind(this);
			
			WCF.System.Event.addListener('com.woltlab.wcf.redactor2', 'validate_' + id, (function (data) {
				if (this.WoltLabSource.isActive()) {
					data.api.throwError(this.$element[0], WCF.Language.get('wcf.editor.source.error.active'));
					data.valid = false;
				}
			}).bind(this));
		},
		
		isActive: function () {
			return (this.$editor[0].style.getPropertyValue('display') === 'none');
		},
		
		format: function (html) {
			var blockTags = ['ul', 'ol', 'li', 'table', 'tbody', 'thead', 'tr', 'td', 'th'];
			this.block.tags.forEach(function(tag) {
				blockTags.push(tag);
			});
			
			blockTags = blockTags.join('|').toLowerCase();
			
			// block tags that are recognized as block tags, but both
			// newline and indentation matches inline elements
			var blocksAsInline = ['p', 'li', 'td', 'th'];
			
			var patternTagAttributes = '[^\'">]*(?:(?:"[^"]*"|\'[^\']*\')[^\'">]*)*';
			
			// protect <pre> from changes
			var backup = [];
			html = html.replace(new RegExp('<pre' + patternTagAttributes + '>[\\s\\S]*?<\/pre>', 'g'), function(match) {
				backup.push(match);
				
				return '@@@WCF_PRE_BACKUP_' + (backup.length - 1) + '@@@';
			});
			
			// normalize whitespace before and after block tags
			html = html.replace(new RegExp('\\s*</(' + blockTags + ')>\\s*', 'g'), function(match, tag) {
				return (blocksAsInline.indexOf(tag) === -1 ? '\n' : '') + '</' + tag + '>';
			});
			html = html.replace(new RegExp('\\s*<(' + blockTags + ')(' + patternTagAttributes + ')>\\s*', 'g'), function(match, tag, attributes) {
				return '\n<' + tag + attributes + '>' + (blocksAsInline.indexOf(tag) === -1 ? '\n' : '');
			});
			
			// Remove empty lines between two adjacent block elements.
			html = html.replace(new RegExp('(<(?:' + blockTags + ')(?:' + patternTagAttributes + ')>\n)\n+(?=<(?:' + blockTags + ')(?:' + patternTagAttributes + ')>)', 'g'), '$1');
			
			// avoid empty newline at quote start
			html = html.replace(/<woltlab-quote([^>]*)>\n\t*\n(\t*)<p/, '<woltlab-quote$1>\n$2<p');
			
			// lists have additional whitespace inside
			html = html.replace(new RegExp('<(ol|ul)(' + patternTagAttributes + ')>\\s*', 'g'), '<$1$2>\n');
			
			// closing lists may have an adjacent closing list item, causing a depth mismatch
			html = html.replace(/(<\/[ou]l>)<\/li>/g, '$1\n</li>');
			
			// split by line break
			var parts = html.split(/\n/);
			var depth = 0;
			var i, length, line;
			var reIsBlockStart = new RegExp('^<(' + blockTags + ')');
			var reIsBlockEnd = new RegExp('^</(' + blockTags + ')>$');
			var increaseDepth = false;
			for (i = 0, length = parts.length; i < length; i++) {
				line = parts[i];
				increaseDepth = false;
				
				if (line.match(reIsBlockStart)) {
					if (blocksAsInline.indexOf(RegExp.$1) === -1) {
						increaseDepth = true;
					}
				}
				else if (line.match(reIsBlockEnd)) {
					if (blocksAsInline.indexOf(RegExp.$1) === -1) {
						depth--;
					}
				}
				
				if (depth > 0) {
					var indent = depth;
					parts[i] = '';
					while (indent--) {
						parts[i] += "\t";
					}
					
					parts[i] += line;
				}
				
				if (increaseDepth) depth++;
			}
			
			html = parts.join("\n");
			
			// reinsert <pre>
			for (i = 0, length = backup.length; i < length; i++) {
				html = html.replace('@@@WCF_PRE_BACKUP_' + i + '@@@', backup[i]);
			}
			
			// remove the trailing newline in front of <pre>
			html = html.replace(/\r?\n<\/pre>/g, '</pre>');
			
			return html.trim();
		}
	};
};
