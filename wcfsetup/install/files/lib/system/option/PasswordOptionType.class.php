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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option
 */
class PasswordOptionType extends TextOptionType {
	/**
	 * @inheritDoc
	 */
	protected $inputType = 'password';
	
	/**
	 * @inheritDoc
	 */
	public function getCondition(PreparedStatementConditionBuilder &$conditions, Option $option, $value) {
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function addCondition(UserList $userList, Option $option, $value) {
		// does nothing
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkUser(User $user, Option $option, $value) {
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getConditionData(Option $option, $newValue) {
		return $newValue;
	}
}
