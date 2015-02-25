<?php
namespace wcf\system\cli\command;

/**
 * Exits WCF.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cli.command
 * @category	Community Framework
 */
class ExitCLICommand implements ICLICommand {
	/**
	 * @see	\wcf\system\cli\command\ICLICommand::execute()
	 */
	public function execute(array $parameters) {
		exit;
	}
	
	/**
	 * @see	\wcf\system\cli\command\ICLICommand::canAccess()
	 */
	public function canAccess() {
		// everyone may access this command
		return true;
	}
}
