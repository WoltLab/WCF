$.Redactor.prototype.WoltLabSize = function() {
	"use strict";
	
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
		},
		
		setSize: function(key) {
			this.selection.save();
			
			require(['WoltLabSuite/Core/Ui/Redactor/Format'], (function(UiRedactorFormat) {
				this.buffer.set();
				
				UiRedactorFormat.format(this.$editor[0], 'font-size', key.replace(/^size_/, '') + 'pt');
				
				this.buffer.set();
			}).bind(this));
			
			this.selection.restore();
		},
		
		removeSize: function() {
			this.selection.save();
			
			require(['WoltLabSuite/Core/Ui/Redactor/Format'], (function(UiRedactorFormat) {
				this.buffer.set();
				
				UiRedactorFormat.removeFormat(this.$editor[0], 'font-size');
				
				this.buffer.set();
			}).bind(this));
			
			this.selection.restore();
		}
	};
};
