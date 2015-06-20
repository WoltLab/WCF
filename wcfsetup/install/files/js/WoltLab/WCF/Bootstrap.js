/**
 * Bootstraps WCF's JavaScript.
 * It defines globals needed for backwards compatibility
 * and runs modules that are needed on page load.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Bootstrap
 */
define(
	[
		'favico',                 'enquire',                'perfect-scrollbar',      'WoltLab/WCF/Date/Time/Relative',
		'UI/SimpleDropdown',      'WoltLab/WCF/UI/Mobile',  'WoltLab/WCF/UI/TabMenu', 'WoltLab/WCF/UI/FlexibleMenu',
		'UI/Dialog',              'WoltLab/WCF/UI/Tooltip', 'WoltLab/WCF/Language',   'WoltLab/WCF/Environment',
		'WoltLab/WCF/Date/Picker'
	], 
	function(
		 favico,                   enquire,                  perfectScrollbar,         DateTimeRelative,
		 UISimpleDropdown,         UIMobile,                 UITabMenu,                UIFlexibleMenu,
		 UIDialog,                 UITooltip,                Language,                 Environment,
		 DatePicker
	)
{
	"use strict";
	
	// perfectScrollbar does not need to be bound anywhere, it just has to be loaded for WCF.js
	window.Favico = favico;
	window.enquire = enquire;
	// non strict equals by intent
	if (window.WCF == null) window.WCF = { };
	if (window.WCF.Language == null) window.WCF.Language = { };
	window.WCF.Language.get = Language.get;
	window.WCF.Language.add = Language.add;
	window.WCF.Language.addObject = Language.addObject;
	
	/**
	 * @exports	WoltLab/WCF/Bootstrap
	 */
	var Bootstrap = {
		/**
		 * Initializes the core UI modifications and unblocks jQuery's ready event.
		 */
		setup: function() {
			Environment.setup();
			
			DateTimeRelative.setup();
			DatePicker.init();
			
			UISimpleDropdown.setup();
			UIMobile.setup();
			UITabMenu.setup();
			UIFlexibleMenu.setup();
			UIDialog.setup();
			UITooltip.setup();
			
			// convert method=get into method=post
			var forms = document.querySelectorAll('form[method=get]');
			for (var i = 0, length = forms.length; i < length; i++) {
				forms[i].setAttribute('method', 'post');
			}
			
			if (Environment.browser() === 'microsoft') {
				window.onbeforeunload = function() {
					/* Prevent "Back navigation caching" (http://msdn.microsoft.com/en-us/library/ie/dn265017%28v=vs.85%29.aspx) */
				};
			}
			
			// DEBUG ONLY
			var interval = 0;
			interval = window.setInterval(function() {
				if (typeof window.jQuery === 'function') {
					window.clearInterval(interval);
					
					window.jQuery.holdReady(false);
				}
			}, 20);
		}
	};
	
	return Bootstrap;
});
