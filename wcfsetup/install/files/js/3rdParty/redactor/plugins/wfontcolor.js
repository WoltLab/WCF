if (!RedactorPlugins) var RedactorPlugins = {};

/**
 * Provides a text color picker, this is actually a heavily modified version of Imperavi's 'fontcolor' plugin.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
RedactorPlugins.wfontcolor = function() {
	"use strict";
	
	return {
		/**
		 * Initializes the RedactorPlugins.wfontcolor plugin.
		 */
		init: function() {
			var $dropdown = this.button.addDropdown(this.button.get('fontcolor'));
			this.wfontcolor._createDropdown($dropdown);
		},
		
		/**
		 * Creates the font color dropdown.
		 * 
		 * @param	jQuery		dropdown
		 */
		_createDropdown: function(dropdown) {
			var $colors = [
				'#000000', '#800000', '#8B4513', '#2F4F4F', '#008080', '#000080', '#4B0082', '#696969',
				'#B22222', '#A52A2A', '#DAA520', '#006400', '#40E0D0', '#0000CD', '#800080', '#808080',
				'#FF0000', '#FF8C00', '#FFD700', '#008000', '#00FFFF', '#0000FF', '#EE82EE', '#A9A9A9',
				'#FFA07A', '#FFA500', '#FFFF00', '#00FF00', '#AFEEEE', '#ADD8E6', '#DDA0DD', '#D3D3D3',
				'#FFF0F5', '#FAEBD7', '#FFFFE0', '#F0FFF0', '#F0FFFF', '#F0F8FF', '#E6E6FA', '#FFFFFF'
			];
			
			var $container = $('<li class="redactorColorPallet" />');
			for (var $i = 0, $length = $colors.length; $i < $length; $i++) {
				var $color = $colors[$i];
				
				var $swatch = $('<a href="#" title="' + $color + '" />').data('color', $color).css('background-color', $color);
				$container.append($swatch);
				$swatch.click($.proxy(this.wfontcolor._onColorPick, this));
			}
			
			var $elNone = $('<a href="#" />').html(this.lang.get('none')).data('color', 'none');
			$elNone.click($.proxy(this.wfontcolor._onColorPick, this));
			
			$container.appendTo(dropdown);
			$('<li class="dropdownDivider" />').appendTo(dropdown);
			$elNone.appendTo(dropdown);
			$elNone.wrap('<li />');
		},
		
		/**
		 * Handles click on a specific text color.
		 * 
		 * @param	object		event
		 */
		_onColorPick: function(event) {
			event.preventDefault();
			
			var $color = $(event.currentTarget).data('color');
			if ($color === 'none') {
				this.inline.removeStyleRule('color');
			}
			else {
				this.inline.format('span', 'style', 'color: ' + $color + ';');
			}
		}
	};
};
