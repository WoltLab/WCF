<?php
namespace wcf\system\option\user;
use wcf\data\user\option\UserOption;
use wcf\data\user\User;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * User option output implementation for the output of a user's birthday.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user
 * @subpackage	system.option.user
 * @category	Community Framework
 */
class BirthdayUserOptionOutput extends DateUserOptionOutput {
	/**
	 * @see	wcf\system\option\user\IUserOptionOutput::getOutput()
	 */
	public function getOutput(User $user, UserOption $option, $value) {
		// set date format
		$this->dateFormat = ($user->birthdayShowYear ? DateUtil::DATE_FORMAT : str_replace('Y', '', WCF::getLanguage()->get(DateUtil::DATE_FORMAT)));
		
		// format date
		$dateString = parent::getOutput($user, $option, $value);
		if ($dateString && $user->birthdayShowYear) {
			$age = DateUtil::getAge($value);
			if ($age > 0) {
				$dateString .= ' ('.$age.')';
			}
		}
			
		return $dateString;
	}
}
