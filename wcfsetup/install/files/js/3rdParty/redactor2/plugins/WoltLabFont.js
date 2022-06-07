$.Redactor.prototype.WoltLabFont = function() {
	"use strict";

	let uiRedactorFormat;
	
	return {
		_fonts: [
			'Arial, Helvetica, sans-serif',
			'Comic Sans MS, Marker Felt, cursive',
			'Consolas, Courier New, Courier, monospace',
			'Georgia, serif',
			'Lucida Sans Unicode, Lucida Grande, sans-serif',
			'Tahoma, Geneva, sans-serif',
			'Times New Roman, Times, serif',
			'Trebuchet MS", Helvetica, sans-serif',
			'Verdana, Geneva, sans-serif'
		],
		
		init: function() {
			var callback = this.WoltLabFont.setFont.bind(this);
			var dropdown = {};
			
			this.WoltLabFont._fonts.forEach(function (font, i) {
				dropdown['fontFamily_' + i] = {
					title: font.split(',')[0].replace(/['"]/g, ''),
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
			var dropdownMenu = button.data('dropdown');
			dropdownMenu.find('a').each((function(index, link) {
				if (link.className.match(/^redactor-dropdown-fontFamily_(\d+)$/)) {
					link.style.setProperty('font-family', this.WoltLabFont._fonts[RegExp.$1], '');
				}
			}).bind(this));
			
			$('<li class="dropdownDivider"></li>').insertBefore(dropdownMenu.children('li').last());

			require(["WoltLabSuite/Core/Ui/Redactor/Format"], (UiRedactorFormat) => {
				uiRedactorFormat = UiRedactorFormat;
			});
		},
		
		setFont(key) {
			key = key.replace(/^fontFamily_/, '');

			if ($.browser.iOS && !this.detect.isIpad()) {
				this.selection.restore();
			}

			this.buffer.set();

			uiRedactorFormat.format(this.$editor[0], 'font-family', this.WoltLabFont._fonts[key]);
		},
		
		removeFont() {
			this.buffer.set();
			
			uiRedactorFormat.removeFormat(this.$editor[0], 'font-family');
		}
	};
};
