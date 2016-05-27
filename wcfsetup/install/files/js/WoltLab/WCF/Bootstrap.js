/**
 * Bootstraps WCF's JavaScript.
 * It defines globals needed for backwards compatibility
 * and runs modules that are needed on page load.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Bootstrap
 */
define(
	[
		'favico',                  'enquire',                'perfect-scrollbar',      'WoltLab/WCF/Date/Time/Relative',
		'Ui/SimpleDropdown',       'WoltLab/WCF/Ui/Mobile',  'WoltLab/WCF/Ui/TabMenu', 'WoltLab/WCF/Ui/FlexibleMenu',
		'Ui/Dialog',               'WoltLab/WCF/Ui/Tooltip', 'WoltLab/WCF/Language',   'WoltLab/WCF/Environment',
		'WoltLab/WCF/Date/Picker', 'EventHandler',           'Core',                   'WoltLab/WCF/Ui/Page/JumpToTop'
	], 
	function(
		 favico,                   enquire,                  perfectScrollbar,         DateTimeRelative,
		 UiSimpleDropdown,         UiMobile,                 UiTabMenu,                UiFlexibleMenu,
		 UiDialog,                 UiTooltip,                Language,                 Environment,
		 DatePicker,               EventHandler,             Core,                     UiPageJumpToTop
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
	
	// WCF.System.Event compatibility
	window.__wcf_bc_eventHandler = EventHandler;
	
	/**
	 * @exports	WoltLab/WCF/Bootstrap
	 */
	return {
		/**
		 * Initializes the core UI modifications and unblocks jQuery's ready event.
		 * 
		 * @param       {Object=}       options         initialization options
		 */
		setup: function(options) {
			options = Core.extend({
				enableMobileMenu: true
			}, options);
			
			Environment.setup();
			
			DateTimeRelative.setup();
			DatePicker.init();
			
			UiSimpleDropdown.setup();
			UiMobile.setup({
				enableMobileMenu: options.enableMobileMenu
			});
			UiTabMenu.setup();
			//UiFlexibleMenu.setup();
			UiDialog.setup();
			UiTooltip.setup();
			
			new UiPageJumpToTop();
			
			// convert method=get into method=post
			var forms = elBySelAll('form[method=get]');
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
});
