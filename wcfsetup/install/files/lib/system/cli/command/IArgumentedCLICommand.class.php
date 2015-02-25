<?php
namespace wcf\system\cli\command;

/**
 * Represents a command with arguments.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cli.command
 * @category	Community Framework
 */
interface IArgumentedCLICommand extends ICLICommand {
	/**
	 * Returns the usage text.
	 * 
	 * @return	string
	 */
	public function getUsage();
}
