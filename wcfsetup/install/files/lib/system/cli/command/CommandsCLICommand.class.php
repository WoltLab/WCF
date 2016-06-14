<?php
namespace wcf\system\cli\command;
use wcf\system\CLIWCF;
use wcf\util\CLIUtil;

/**
 * Lists available commands.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cli\Command
 */
class CommandsCLICommand implements ICLICommand {
	/**
	 * @inheritDoc
	 */
	public function execute(array $parameters) {
		CLIWCF::getReader()->println(CLIUtil::generateList(array_keys(CLICommandHandler::getCommands())));
	}
	
	/**
	 * @inheritDoc
	 */
	public function canAccess() {
		return true;
	}
}
