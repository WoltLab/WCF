/**
 * Bootstraps WCF's JavaScript with additions for the ACP usage.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Acp/Bootstrap
 */
define(['WoltLab/WCF/Bootstrap', './Ui/Page/Menu'], function(Bootstrap, UiPageMenu) {
	"use strict";
	
	/**
	 * @exports	WoltLab/WCF/Acp/Bootstrap
	 */
	return {
		/**
		 * Bootstraps general modules and frontend exclusive ones.
		 * 
		 * @param	{Object}	options		bootstrap options
		 */
		setup: function(options) {
			Bootstrap.setup();
			UiPageMenu.init();
		}
	};
});
