if (!RedactorPlugins) var RedactorPlugins = {};

/**
 * Provides a font size picker, this is actually a heavily modified version of Imperavi's 'fontsize' plugin.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
RedactorPlugins.wfontsize = {
	init: function () {
		var $fontSizes = [ 8, 10, 12, 14, 18, 24, 36 ];
		
		var $dropdown = { };
		var self = this;
		for (var $i = 0, $length = $fontSizes.length; $i < $length; $i++) {
			var $fontSize = $fontSizes[$i];
			
			$dropdown['fontSize' + $i] = {
				title: $fontSize,
				className: 'wfontsize-' + $fontSize,
				fontSize: $fontSize,
				callback: function(name, button, object, event) {
					self.inlineSetStyle('font-size', object.fontSize + 'pt');
				}
			};
		}
		
		$dropdown['separator'] = { name: 'separator' };
		$dropdown['remove'] = {
			title: 'remove font size',
			callback: function() {
				this.inlineRemoveStyle('font-size');
			}
		};
		
		this.buttonReplace('fontsize', 'wfontsize', 'Change font size', false, $dropdown);
		this.buttonGet('wfontsize').addClass('re-fontsize');
		
		// modify dropdown to reflect each font family
		$dropdown = this.$toolbar.find('.redactor_dropdown_box_wfontsize');
		for (var $i = 0, $length = $fontSizes.length; $i < $length; $i++) {
			var $fontSize = $fontSizes[$i];
			
			var $listItem = $dropdown.children('a.wfontsize-' + $fontSize).removeClass('wfontsize-' + $fontSizes).css('font-size', $fontSize + 'pt');
			if ($fontSize > 18) {
				$listItem.css('line-height', '1em');
			}
		}
	}
};
