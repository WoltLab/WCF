<?php
namespace wcf\system\cli\command;

/**
 * Every command has to implement this interface.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cli\Command
 */
interface ICLICommand {
	/**
	 * Executes the command.
	 * 
	 * @param	array		$parameters
	 */
	public function execute(array $parameters);
	
	/**
	 * Returns true if the user is allowed to use this command.
	 * 
	 * @return	boolean
	 */
	public function canAccess();
}
