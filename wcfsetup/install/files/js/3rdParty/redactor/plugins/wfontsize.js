if (!RedactorPlugins) var RedactorPlugins = {};

/**
 * Provides a font size picker, this is actually a heavily modified version of Imperavi's 'fontsize' plugin.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
RedactorPlugins.wfontsize = function() {
	"use strict";
	
	return {
		/**
		 * Initializes the RedactorPlugins.wfontsize plugin.
		 */
		init: function () {
			var $dropdown = this.button.addDropdown(this.button.get('fontsize'));
			this.wfontsize._createDropdown($dropdown);
		},
		
		/**
		 * Creates the font size dropdown.
		 * 
		 * @param	jQuery		dropdown
		 */
		_createDropdown: function(dropdown) {
			var $fontSizes = [ 8, 10, 12, 14, 18, 24, 36 ];
			var self = this;
			for (var $i = 0; $i < $fontSizes.length; $i++) {
				var $fontSize = $fontSizes[$i];
				var $listItem = $('<li><a href="#">' + $fontSize + '</a></li>').appendTo(dropdown);
				var $item = $listItem.children('a').data('fontSize', $fontSize).css('font-size', $fontSize + 'pt');
				if ($fontSize > 18) {
					$item.css('line-height', '1em');
				}
				
				$item.click(function(event) {
					event.preventDefault();
					
					self.inline.format('span', 'style', 'font-size: ' + $(this).data('fontSize') + 'pt;');
				});
			}
			
			$('<li class="dropdownDivider" />').appendTo(dropdown);
			var $listItem = $('<li><a href="#">' + this.opts.curLang.none + '</a></li>').appendTo(dropdown);
			$listItem.children('a').click(function(event) {
				event.preventDefault();
				
				self.inline.removeStyleRule('font-size');
			});
		}
	};
};
