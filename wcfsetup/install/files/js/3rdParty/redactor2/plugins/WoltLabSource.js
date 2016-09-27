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
			
			html = html.replace(new RegExp('<(' + blockTags + ')([^>]*)>\s*', 'g'), '<$1$2>\n\t');
			html = html.replace(new RegExp('\s*</(' + blockTags + ')>', 'g'), '\n</$1>');
			
			html = html.replace(/\s*<li>/g, '\n<li>');
			html = html.replace(/<\/li>\s*/g, '</li>\n');
			html = html.replace(/<li>([\s\S]*?)<\/li>/g, function(match, content) {
				var parts = content.trim().split('\n');
				return '<li>\n\t' + parts.join('\n\t') + '\n</li>';
			});
			
			html = html.replace(/\s*<\/p>\s*/g, '\n</p>\n');
			html = html.replace(/<p>\s*<br>\s*<\/p>/g, '<p><br></p>');
			
			return html.replace(/^\s+/, '');
		}
	};
};
