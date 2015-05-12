/**
 * Bootstraps WCF's JavaScript with additions for the frontend usage.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/BootstrapFrontend
 */
define(['WoltLab/WCF/Bootstrap', 'WoltLab/WCF/Controller/Sitemap', 'WoltLab/WCF/Controller/Style/Changer'], function(Bootstrap, ControllerSitemap, ControllerStyleChanger) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function BootstrapFrontend() {}
	BootstrapFrontend.prototype = {
		/**
		 * Bootstraps general modules and frontend exclusive ones.
		 * 
		 * @param	{object<string, *>}	options		bootstrap options
		 */
		setup: function(options) {
			Bootstrap.setup();
			
			ControllerSitemap.setup();
			
			if (options.styleChanger) {
				ControllerStyleChanger.setup();
			}
		}
	};
	
	return new BootstrapFrontend();
});
