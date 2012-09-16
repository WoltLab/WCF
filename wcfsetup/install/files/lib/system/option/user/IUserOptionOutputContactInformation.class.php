<?php
namespace wcf\system\option\user;
use wcf\data\user\option\UserOption;
use wcf\data\user\User;

/**
 * Any user option output class should implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user
 * @category	Community Framework
 */
interface IUserOptionOutputContactInformation {
	/**
	 * Returns the output data of this user option.
	 * 
	 * @param	wcf\data\user\User			$user
	 * @param	wcf\data\user\option\UserOption		$optionData
	 * @param	string					$value
	 * @return	array
	 */
	public function getOutputData(User $user, UserOption $option, $value);
} 
?>
