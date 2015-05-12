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
		'jquery',            'favico',                 'enquire',                'WoltLab/WCF/Date/Time/Relative',
		'UI/SimpleDropdown', 'WoltLab/WCF/UI/Mobile',  'WoltLab/WCF/UI/TabMenu', 'WoltLab/WCF/UI/FlexibleMenu',
		'UI/Dialog',         'WoltLab/WCF/UI/Tooltip', 'WoltLab/WCF/Controller/Sitemap'
	], 
	function(
		 $,                   favico,                   enquire,                  relativeTime,
		 simpleDropdown,      UIMobile,                 UITabMenu,                UIFlexibleMenu,
		 UIDialog,            UITooltip,                ControllerSitemap
	)
{
	"use strict";
	
	window.Favico = favico;
	window.enquire = enquire;
	
	/**
	 * @constructor
	 */
	function Bootstrap() { }
	Bootstrap.prototype = {
		/**
		 * Initializes the core UI modifications and unblocks jQuery's ready event.
		 */
		setup: function() {
			relativeTime.setup();
			simpleDropdown.setup();
			UIMobile.setup();
			UITabMenu.setup();
			UIFlexibleMenu.setup();
			UIDialog.setup();
			UITooltip.setup();
			
			ControllerSitemap.setup();
			
			$.holdReady(false);
		}
	};
	
	return new Bootstrap();
});
