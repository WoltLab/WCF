<?php
namespace wcf\system\option\user;
use wcf\data\user\option\UserOption;
use wcf\data\user\User;
use wcf\data\user\UserProfile;

/**
 * User option output implementation for the output of a user's birthday.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user
 * @category	Community Framework
 */
class BirthdayUserOptionOutput extends DateUserOptionOutput {
	/**
	 * @see	\wcf\system\option\user\IUserOptionOutput::getOutput()
	 */
	public function getOutput(User $user, UserOption $option, $value) {
		$profile = new UserProfile($user);
		return $profile->getBirthday();
	}
}
