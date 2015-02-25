<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * Option type implementation for password input fields.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class PasswordOptionType extends TextOptionType {
	/**
	 * @see	\wcf\system\option\TextOptionType::$inputType
	 */
	protected $inputType = 'password';
	
	/**
	 * @see	\wcf\system\option\ISearchableUserOption::getCondition()
	 */
	public function getCondition(PreparedStatementConditionBuilder &$conditions, Option $option, $value) {
		return false;
	}
	
	/**
	 * @see	\wcf\system\option\ISearchableConditionUserOption::addCondition()
	 */
	public function addCondition(UserList $userList, Option $option, $value) {
		// does nothing
	}
	
	/**
	 * @see	\wcf\system\option\ISearchableConditionUserOption::checkUser()
	 */
	public function checkUser(User $user, Option $option, $value) {
		return false;
	}
	
	/**
	 * @see	\wcf\system\option\ISearchableConditionUserOption::getConditionData()
	 */
	public function getConditionData(Option $option, $newValue) {
		return $newValue;
	}
}
