if (!RedactorPlugins) var RedactorPlugins = {};

/**
 * Provides a font size picker, this is actually a heavily modified version of Imperavi's 'fontsize' plugin.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
RedactorPlugins.wfontsize = {
	/**
	 * Initializes the RedactorPlugins.wfontsize plugin.
	 */
	init: function () {
		var $dropdown = this._createFontSizeDropdown();
		
		this.buttonReplace('fontsize', 'wfontsize', WCF.Language.get('wcf.bbcode.button.fontSize'), $.proxy(function(btnName, $button, btnObject, e) {
			this.dropdownShow(e, btnName);
		}, this));
		this.buttonGet('wfontsize').addClass('re-fontsize').data('dropdown', $dropdown);
	},
	
	/**
	 * Creates the font size dropdown.
	 */
	_createFontSizeDropdown: function() {
		var $dropdown = $('<div class="redactor_dropdown redactor_dropdown_box_wfontsize dropdownMenu" style="display: none;">');
		var $fontSizes = [ 8, 10, 12, 14, 18, 24, 36 ];
		var self = this;
		for (var $i = 0; $i < $fontSizes.length; $i++) {
			var $fontSize = $fontSizes[$i];
			var $listItem = $('<li><a href="#">' + $fontSize + '</a></li>').appendTo($dropdown);
			var $item = $listItem.children('a').data('fontSize', $fontSize).css('font-size', $fontSize + 'pt');
			if ($fontSize > 18) {
				$item.css('line-height', '1em');
			}
			
			$item.click(function() {
				event.preventDefault();
				
				self.inlineSetStyle('font-size', $(this).data('fontSize') + 'pt');
			});
		}
		
		$('<li class="dropdownDivider" />').appendTo($dropdown);
		var $listItem = $('<li><a href="#">None</a></li>').appendTo($dropdown);
		$listItem.children('a').click(function() {
			event.preventDefault();
			
			self.inlineRemoveStyle('font-size');
		});
		
		$(this.$toolbar).append($dropdown);
		
		return $dropdown;
	}
};
