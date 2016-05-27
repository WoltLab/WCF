$.Redactor.prototype.WoltLabColor = function() {
	"use strict";
	
	return {
		init: function() {
			// these are hex values, but the '#' was left out for convenience
			var colors = [
				'000000', '800000', '8B4513', '2F4F4F', '008080', '000080', '4B0082', '696969',
				'B22222', 'A52A2A', 'DAA520', '006400', '40E0D0', '0000CD', '800080', '808080',
				'FF0000', 'FF8C00', 'FFD700', '008000', '00FFFF', '0000FF', 'EE82EE', 'A9A9A9',
				'FFA07A', 'FFA500', 'FFFF00', '00FF00', 'AFEEEE', 'ADD8E6', 'DDA0DD', 'D3D3D3',
				'FFF0F5', 'FAEBD7', 'FFFFE0', 'F0FFF0', 'F0FFFF', 'F0F8FF', 'E6E6FA', 'FFFFFF'
			];
			
			var callback = this.WoltLabColor.setColor.bind(this), color;
			var dropdown = {
				'removeColor': {
					title: 'remove color',
					func: this.WoltLabColor.removeColor.bind(this)
				}
			};
			for (var i = 0, length = colors.length; i < length; i++) {
				color = colors[i];
				
				dropdown['color_' + color] = {
					title: '#' + color,
					func: callback
				};
			}
			
			var button = this.button.add('woltlabColor', '');
			this.button.addDropdown(button, dropdown);
			
			// add styling
			button.data('dropdown').find('a').each(function(index, link) {
				if (link.className.match(/redactor-dropdown-color_([A-F0-9]{6})/)) {
					link.parentNode.classList.add('woltlab-color-' + RegExp.$1);
					link.parentNode.classList.add('woltlab-color-selection');
				}
			});
			
		},
		
		setColor: function(key) {
			key = key.replace(/^color_/, '');
			
			require(['WoltLab/WCF/Ui/Redactor/Format'], (function(UiRedactorFormat) {
				this.selection.save();
				
				UiRedactorFormat.format(this.$editor[0], 'woltlab-color', 'woltlab-color-' + key);
				
				this.selection.restore();
			}).bind(this));
		},
		
		removeColor: function() {
			require(['WoltLab/WCF/Ui/Redactor/Format'], (function(UiRedactorFormat) {
				this.selection.save();
				
				UiRedactorFormat.removeFormat(this.$editor[0], 'woltlab-color');
				
				this.selection.restore();
			}).bind(this));
		}
	};
};
