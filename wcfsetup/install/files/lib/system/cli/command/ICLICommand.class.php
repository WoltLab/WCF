<?php
namespace wcf\system\cli\command;

/**
 * Every command has to implement this interface.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cli.command
 * @category	Community Framework
 */
interface ICLICommand {
	/**
	 * Executes the command.
	 */
	public function execute(array $parameters);
	
	/**
	 * Returns true if the user is allowed to use this command.
	 * 
	 * @return	boolean
	 */
	public function canAccess();
}
