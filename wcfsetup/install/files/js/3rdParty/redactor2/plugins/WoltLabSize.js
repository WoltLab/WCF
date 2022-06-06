$.Redactor.prototype.WoltLabSize = function() {
	"use strict";

	let uiRedactorFormat;
	
	return {
		init: function() {
			var size, sizes = [8, 10, 12, 14, 18, 24, 36];
			var callback = this.WoltLabSize.setSize.bind(this);
			var dropdown = {};
			
			for (var i = 0, length = sizes.length; i < length; i++) {
				size = sizes[i];
				
				dropdown['size_' + size] = {
					title: size,
					func: callback
				};
			}
			
			dropdown['removeSize'] = {
				title: this.lang.get('remove-size'),
				func: this.WoltLabSize.removeSize.bind(this)
			};
			
			var button = this.button.add('woltlabSize', '');
			this.button.addDropdown(button, dropdown);
			
			// add styling
			var dropdownMenu = button.data('dropdown');
			dropdownMenu.find('a').each(function(index, link) {
				if (link.className.match(/redactor-dropdown-size_(\d{1,2})/)) {
					link.style.setProperty('font-size', RegExp.$1 + 'pt', '');
					link.parentNode.classList.add('woltlab-size-selection');
				}
			});
			
			$('<li class="dropdownDivider"></li>').insertBefore(dropdownMenu.children('li').last());

			require(['WoltLabSuite/Core/Ui/Redactor/Format'], (UiRedactorFormat) => {
				uiRedactorFormat = UiRedactorFormat;
			});
		},
		
		setSize(key) {
			this.buffer.set();

			if ($.browser.iOS && !this.detect.isIpad()) {
				this.selection.restore();
			}
			
			uiRedactorFormat.format(this.$editor[0], 'font-size', key.replace(/^size_/, '') + 'pt');
		},
		
		removeSize() {
			this.buffer.set();
			
			uiRedactorFormat.removeFormat(this.$editor[0], 'font-size');
		}
	};
};
