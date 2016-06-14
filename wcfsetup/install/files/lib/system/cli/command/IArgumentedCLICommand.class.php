<?php
namespace wcf\system\cli\command;

/**
 * Represents a command with arguments.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cli\Command
 */
interface IArgumentedCLICommand extends ICLICommand {
	/**
	 * Returns the usage text.
	 * 
	 * @return	string
	 */
	public function getUsage();
}
