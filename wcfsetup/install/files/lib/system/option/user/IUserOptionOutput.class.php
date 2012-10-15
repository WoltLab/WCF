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
interface IUserOptionOutput {
	/**
	 * Returns a short version of the html code for the output of the given user option.
	 * 
	 * @param	wcf\data\user\User			$user
	 * @param	wcf\data\user\option\UserOption		$option
	 * @param	string					$value
	 * @return	string
	 */
	public function getShortOutput(User $user, UserOption $option, $value);
	
	/**
	 * Returns a medium version of the html code for the output of the given user option.
	 * 
	 * @param	wcf\data\user\User			$user
	 * @param	wcf\data\user\option\UserOption		$option
	 * @param	string					$value
	 * @return	string
	 */
	public function getMediumOutput(User $user, UserOption $option, $value);
	
	/**
	 * Returns the html code for the output of the given user option.
	 * 
	 * @param	wcf\data\user\User			$user
	 * @param	wcf\data\user\option\UserOption		$option
	 * @param	string					$value
	 * @return	string
	 */
	public function getOutput(User $user, UserOption $option, $value);
}
?>
