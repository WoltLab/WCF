$.Redactor.prototype.WoltLabSource = function() {
	"use strict";
	
	return {
		init: function () {
			// disable caret position in source mode
			this.source.setCaretOnShow = function () {};
			this.source.setCaretOnHide = function (html) { return html; };
			
			var mpHide = this.source.hide;
			this.source.hide = (function () {
				mpHide.call(this);
				
				setTimeout(this.focus.end.bind(this), 100);
				
				this.placeholder.enable();
			}).bind(this);
			
			var textarea = this.source.$textarea[0];
			
			// move textarea in front of the original textarea
			this.$element[0].parentNode.insertBefore(textarea, this.$element[0]);
			
			var mpShow = this.source.show;
			this.source.show = (function () {
				// fix height
				var height = this.$editor[0].offsetHeight;
				
				mpShow.call(this);
				
				textarea.style.setProperty('height', Math.ceil(height) + 'px', '');
				textarea.style.setProperty('display', 'block', '');
				
				textarea.value = this.WoltLabSource.format(textarea.value);
				
				textarea.selectionStart = textarea.selectionEnd = textarea.value.length;
			}).bind(this);
			
			WCF.System.Event.addListener('com.woltlab.wcf.redactor2', 'validate_' + this.$element[0].id, (function (data) {
				if (textarea.clientHeight) {
					data.api.throwError(this.$element[0], WCF.Language.get('wcf.editor.source.error.active'));
					data.valid = false;
				}
			}).bind(this));
		},
		
		isActive: function () {
			return (this.$editor[0].style.getPropertyValue('display') === 'none');
		},
		
		format: function (html) {
			var blockTags = this.block.tags.join('|').toLowerCase();
			blockTags += '|ul|ol|li';
			
			var patternTagAttributes = '[^\'">]*(?:(?:"[^"]*"|\'[^\']*\')[^\'">]*)*';
			
			// protect <pre> from changes
			var backup = [];
			html = html.replace(new RegExp('<pre' + patternTagAttributes + '>[\s\S]*?<\/pre>', 'g'), function(match) {
				backup.push(match);
				
				return '@@@WCF_PRE_BACKUP_' + (backup.length - 1) + '@@@';
			});
			
			// normalize whitespace before and after block tags
			html = html.replace(new RegExp('\\s*</(' + blockTags + ')(' + patternTagAttributes + ')>\\s*', 'g'), '\n</$1$2>');
			html = html.replace(new RegExp('\\s*<(' + blockTags + ')(' + patternTagAttributes + ')>\\s*', 'g'), '\n<$1$2>\n');
			
			// lists have additional whitespace inside
			html = html.replace(new RegExp('<(ol|ul)(' + patternTagAttributes + ')>\\s*', 'g'), '<$1$2>\n');
			
			// split by line break
			var parts = html.split(/\n/);
			var depth = 0;
			var i, length, line;
			var reIsBlockStart = new RegExp('^<(?:' + blockTags + ')');
			var reIsBlockEnd = new RegExp('^</(?:' + blockTags + ')>$');
			var increaseDepth = false;
			for (i = 0, length = parts.length; i < length; i++) {
				line = parts[i];
				increaseDepth = false;
				
				if (line.match(reIsBlockStart)) {
					increaseDepth = true;
				}
				else if (line.match(reIsBlockEnd)) {
					depth--;
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
			
			return html.trim();
		}
	};
};
