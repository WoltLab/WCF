/**
 * Class and function collection for WCF ACP
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

/**
 * Initialize WCF.ACP namespace
 */
WCF.ACP = { };

/**
 * Namespace for ACP application management.
 */
WCF.ACP.Application = { };

/**
 * Namespace for ACP package management.
 */
WCF.ACP.Package = { };

/**
 * Worker support for ACP.
 * 
 * @param	string		dialogID
 * @param	string		className
 * @param	string		title
 * @param	object		parameters
 * @param	object		callback
 * 
 * @deprecated  3.1 - please use `WoltLabSuite/Core/Acp/Ui/Worker` instead
 */
WCF.ACP.Worker = Class.extend({
	/**
	 * Initializes a new worker instance.
	 * 
	 * @param	string		dialogID
	 * @param	string		className
	 * @param	string		title
	 * @param	object		parameters
	 * @param	object		callback
	 */
	init: function(dialogID, className, title, parameters, callback) {
		if (typeof callback === 'function') {
			throw new Error("The callback parameter is no longer supported, please migrate to 'WoltLabSuite/Core/Acp/Ui/Worker'.");
		}
		
		require(['WoltLabSuite/Core/Acp/Ui/Worker'], function(AcpUiWorker) {
			new AcpUiWorker({
				// dialog
				dialogId: dialogID,
				dialogTitle: title,
				
				// ajax
				className: className,
				parameters: parameters
			});
		});
	}
});
