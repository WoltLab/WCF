<?php
namespace wcf\system\cli\command;
use wcf\system\CLIWCF;
use wcf\util\CLIUtil;

/**
 * Lists available commands.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cli.command
 * @category	Community Framework
 */
class CommandsCLICommand implements ICLICommand {
	/**
	 * @see	\wcf\system\cli\command\ICLICommand::execute()
	 */
	public function execute(array $parameters) {
		CLIWCF::getReader()->println(CLIUtil::generateList(array_keys(CLICommandHandler::getCommands())));
	}
	
	/**
	 * @see	\wcf\system\cli\command\ICLICommand::canAccess()
	 */
	public function canAccess() {
		return true;
	}
}
