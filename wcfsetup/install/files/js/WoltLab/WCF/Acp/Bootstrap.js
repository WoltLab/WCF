/**
 * Bootstraps WCF's JavaScript with additions for the ACP usage.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Acp/Bootstrap
 */
define(['WoltLab/WCF/Bootstrap'], function(Bootstrap) {
	"use strict";
	
	/**
	 * ACP Boostrapper.
	 * 
	 * @exports	WoltLab/WCF/Acp/Bootstrap
	 */
	var AcpBootstrap = {
		/**
		 * Bootstraps general modules and frontend exclusive ones.
		 * 
		 * @param	{object<string, *>}	options		bootstrap options
		 */
		setup: function(options) {
			Bootstrap.setup();
		}
	};
	
	return AcpBootstrap;
});
