if (!RedactorPlugins) var RedactorPlugins = {};

/**
 * Provides a font family picker, this is actually a heavily modified version of Imperavi's 'fontfamily' plugin.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
RedactorPlugins.wfontfamily = function() {
	"use strict";
	
	return {
		/**
		 * Initializes the RedactorPlugins.wfontsize plugin.
		 */
		init: function () {
			var $dropdown = this.button.addDropdown(this.button.get('fontfamily'));
			this.wfontfamily._createDropdown($dropdown);
		},
		
		/**
		 * Creates the font family dropdown.
		 * 
		 * @param	jQuery		dropdown
		 */
		_createDropdown: function(dropdown) {
			var $fonts = {
				'Arial': "Arial, Helvetica, sans-serif",
				'Comic Sans MS': "Comic Sans MS, cursive",
				'Courier New': "Consolas, Courier New, Courier, monospace",
				'Georgia': "Georgia, serif",
				'Lucida Sans Unicode': "Lucida Sans Unicode, Lucida Grande, sans-serif",
				'Tahoma': "Tahoma, Geneva, sans-serif",
				'Times New Roman': "Times New Roman, Times, serif",
				'Trebuchet MS': "Trebuchet MS, Helvetica, sans-serif",
				'Verdana': "Verdana, Geneva, sans-serif"
			};
			var self = this;
			$.each($fonts, function(title, fontFamily) {
				var $listItem = $('<li><a href="#">' + title + '</a></li>').appendTo(dropdown);
				var $item = $listItem.children('a').data('fontFamily', fontFamily).css('font-family', fontFamily);
				$item.click(function(event) {
					event.preventDefault();
					
					self.inline.format('span', 'style', 'font-family: ' + $(this).data('fontFamily') + ';');
				});
			});
			
			$('<li class="dropdownDivider" />').appendTo(dropdown);
			var $listItem = $('<li><a href="#">' + this.lang.get('none') + '</a></li>').appendTo(dropdown);
			$listItem.children('a').click(function(event) {
				event.preventDefault();
				
				self.inline.removeStyleRule('font-family');
			});
		}
	};
};
