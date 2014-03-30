if (!RedactorPlugins) var RedactorPlugins = {};

/**
 * Provides a font family picker, this is actually a heavily modified version of Imperavi's 'fontfamily' plugin.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
RedactorPlugins.wfontfamily = {
	init: function () {
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
		
		var $dropdown = { };
		var $i = 0;
		var self = this;
		$.each($fonts, function(title, value) {
			$dropdown['fontFamily' + $i] = {
				title: title,
				className: 'wfontfamily-' + $i,
				callback: function() {
					self.inlineSetStyle('font-family', value);
				}
			};
			
			$i++;
		});
		$dropdown['separator'] = { name: 'separator' };
		$dropdown['remove'] = {
			title: 'remove font',
			callback: function() {
				this.inlineRemoveStyle('font-family');
			}
		};
		
		this.buttonReplace('fontfamily', 'wfontfamily', 'Change font family', false, $dropdown);
		this.buttonGet('wfontfamily').addClass('re-fontfamily');
		
		// modify dropdown to reflect each font family
		$dropdown = this.$toolbar.find('.redactor_dropdown_box_wfontfamily');
		$i = 0;
		$.each($fonts, function(title, value) {
			$dropdown.children('.wfontfamily-' + $i).removeClass('wfontfamily-' + $i).css('font-family', value);
			$i++;
		});
	}
};