if (!RedactorPlugins) var RedactorPlugins = {};

/**
 * Provides a font family picker, this is actually a heavily modified version of Imperavi's 'fontfamily' plugin.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
RedactorPlugins.wfontfamily = {
	/**
	 * Initializes the RedactorPlugins.wfontsize plugin.
	 */
	init: function () {
		var $dropdown = this._createFontFamilyDropdown();
		
		this.buttonReplace('fontfamily', 'wfontfamily', WCF.Language.get('wcf.bbcode.button.fontFamily'), $.proxy(function(btnName, $button, btnObject, e) {
			this.dropdownShow(e, btnName);
		}, this));
		this.buttonGet('wfontfamily').addClass('re-fontfamily').data('dropdown', $dropdown);
	},
	
	/**
	 * Creates the font family dropdown.
	 */
	_createFontFamilyDropdown: function() {
		var $dropdown = $('<div class="redactor_dropdown redactor_dropdown_box_wfontfamily dropdownMenu" style="display: none;">');
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
			var $listItem = $('<li><a href="#">' + title + '</a></li>').appendTo($dropdown);
			var $item = $listItem.children('a').data('fontFamily', fontFamily).css('font-family', fontFamily);
			$item.click(function() {
				event.preventDefault();
				
				self.inlineSetStyle('font-family', $(this).data('fontFamily'));
			});
		});
		
		$('<li class="dropdownDivider" />').appendTo($dropdown);
		var $listItem = $('<li><a href="#">None</a></li>').appendTo($dropdown);
		$listItem.children('a').click(function() {
			event.preventDefault();
			
			self.inlineRemoveStyle('font-family');
		});
		
		$(this.$toolbar).append($dropdown);
		
		return $dropdown;
	}
};