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
				title: 'remove size',
				func: this.WoltLabSize.removeSize.bind(this)
			};
			
			var button = this.button.add('woltlabSize', '');
			this.button.addDropdown(button, dropdown);
			
			// add styling
			button.data('dropdown').find('a').each(function(index, link) {
				if (link.className.match(/redactor-dropdown-size_(\d{1,2})/)) {
					link.parentNode.classList.add('woltlab-size-' + RegExp.$1);
					link.parentNode.classList.add('woltlab-size-selection');
				}
			});
			
		},
		
		setSize: function(key) {
			require(['WoltLab/WCF/Ui/Redactor/Format'], (function(UiRedactorFormat) {
				this.selection.save();
				
				UiRedactorFormat.format(this.$editor[0], 'woltlab-size', 'woltlab-size-' + key.replace(/^size_/, ''));
				
				this.selection.restore();
			}).bind(this));
		},
		
		removeSize: function() {
			require(['WoltLab/WCF/Ui/Redactor/Format'], (function(UiRedactorFormat) {
				this.selection.save();
				
				UiRedactorFormat.removeFormat(this.$editor[0], 'woltlab-size');
				
				this.selection.restore();
			}).bind(this));
		}
	};
};
