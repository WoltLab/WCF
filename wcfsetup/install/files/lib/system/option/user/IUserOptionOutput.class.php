<?php
namespace wcf\system\option\user;
use wcf\data\user\option\UserOption;
use wcf\data\user\User;

/**
 * Any user option output class should implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option\User
 */
interface IUserOptionOutput {
	/**
	 * Returns the html code for the output of the given user option.
	 * 
	 * @param	\wcf\data\user\User			$user
	 * @param	\wcf\data\user\option\UserOption		$option
	 * @param	string					$value
	 * @return	string
	 */
	public function getOutput(User $user, UserOption $option, $value);
}
