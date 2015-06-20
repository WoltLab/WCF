/**
 * Bootstraps WCF's JavaScript with additions for the ACP usage.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/ACP/Bootstrap
 */
define(['WoltLab/WCF/Bootstrap', 'WoltLab/WCF/Date/Picker'], function(Bootstrap, DatePicker) {
	"use strict";
	
	/**
	 * ACP Boostrapper.
	 * 
	 * @exports	WoltLab/WCF/ACP/Bootstrap
	 */
	var ACPBootstrap = {
		/**
		 * Bootstraps general modules and frontend exclusive ones.
		 * 
		 * @param	{object<string, *>}	options		bootstrap options
		 */
		setup: function(options) {
			Bootstrap.setup();
			DatePicker.init();
		}
	};
	
	return ACPBootstrap;
});
