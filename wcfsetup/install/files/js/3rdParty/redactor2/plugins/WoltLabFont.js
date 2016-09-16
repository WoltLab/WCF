$.Redactor.prototype.WoltLabFont = function() {
	"use strict";
	
	return {
		init: function() {
			var fonts = ['arial', 'comicSansMs', 'courierNew', 'georgia', 'lucidaSansUnicode', 'tahoma', 'timesNewRoman', 'trebuchetMs', 'verdana']; 
			var fontNames = ['Arial', 'Comic Sans MS', 'Courier New', 'Georgia', 'Lucida Sans Unicode', 'Tahoma', 'Times New Roman', 'Trebuchet MS', 'Verdana'];
			
			var callback = this.WoltLabFont.setFont.bind(this);
			var dropdown = {};
			
			fonts.forEach(function (font, i) {
				dropdown[font] = {
					title: fontNames[i],
					func: callback
				};
			});
			
			dropdown['removeFont'] = {
				title: this.lang.get('remove-font'),
				func: this.WoltLabFont.removeFont.bind(this)
			};
			
			var button = this.button.add('woltlabFont', '');
			this.button.addDropdown(button, dropdown);
			
			// add styling
			button.data('dropdown').find('a').each(function(index, link) {
				if (link.className && link.className !== 'redactor-dropdown-removeFont') {
					link.parentNode.classList.add('woltlab-font-' + link.className.replace(/^redactor-dropdown-/, ''));
					link.parentNode.classList.add('woltlab-font-selection');
				}
			});
			
			WCF.System.Event.addListener('com.woltlab.wcf.redactor2', 'convertTags_' + this.$element[0].id, function (data) {
				elBySelAll('woltlab-font', data.div, function (element) {
					if (element.className.match(/^woltlab-font-([a-zA-Z]+)$/)) {
						if (fonts.indexOf(RegExp.$1) !== -1) {
							data.addToStorage(element, ['class']);
						}
					}
				});
			});
		},
		
		setFont: function(key) {
			this.selection.save();
			
			require(['WoltLabSuite/Core/Ui/Redactor/Format'], (function(UiRedactorFormat) {
				this.buffer.set();
				
				UiRedactorFormat.format(this.$editor[0], 'woltlab-font', 'woltlab-font-' + key);
				
				this.buffer.set();
			}).bind(this));
		},
		
		removeFont: function() {
			this.selection.save();
			
			require(['WoltLabSuite/Core/Ui/Redactor/Format'], (function(UiRedactorFormat) {
				this.buffer.set();
				
				UiRedactorFormat.removeFormat(this.$editor[0], 'woltlab-font');
				
				this.buffer.set();
			}).bind(this));
		}
	};
};
