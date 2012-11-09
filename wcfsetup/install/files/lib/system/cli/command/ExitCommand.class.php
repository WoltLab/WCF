<?php
namespace wcf\system\cli\command;

/**
 * Exits WCF.
 *
 * @author	Tim Düsterhus
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cli.command
 * @category	Community Framework
 */
class ExitCommand implements ICommand {
	/**
	 * @see \wcf\system\cli\command\ICommand::execute()
	 */
	public function execute() {
		exit;
	}
	
	/**
	 * Everyone may access this command.
	 * @see \wcf\system\cli\command\ICommand::canAccess()
	 */
	public function canAccess() {
		return true;
	}
}