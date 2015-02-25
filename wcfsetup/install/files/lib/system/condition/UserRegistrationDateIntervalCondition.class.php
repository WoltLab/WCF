<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\WCF;

/**
 * Condition implementation for a relative interval for the registration date of
 * a user.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 */
class UserRegistrationDateIntervalCondition extends AbstractIntegerCondition implements IContentCondition, IUserCondition {
	/**
	 * @see	\wcf\system\condition\AbstractMultipleFieldsCondition::$languageItemPrefix
	 */
	protected $label = 'wcf.user.condition.registrationDateInterval';
	
	/**
	 * @see	\wcf\system\condition\AbstractIntegerCondition::$minValue
	 */
	protected $minValue = 0;
	
	/**
	 * @see	\wcf\system\condition\IUserCondition::addUserCondition()
	 */
	public function addUserCondition(Condition $condition, UserList $userList) {
		if ($condition->greaterThan !== null) {
			$userList->getConditionBuilder()->add('user_table.registrationDate < ?', array(TIME_NOW - $condition->greaterThan * 86400));
		}
		if ($condition->lessThan !== null) {
			$userList->getConditionBuilder()->add('user_table.registrationDate > ?', array(TIME_NOW - $condition->lessThan * 86400));
		}
	}
	
	/**
	 * @see	\wcf\system\condition\IUserCondition::checkUser()
	 */
	public function checkUser(Condition $condition, User $user) {
		if ($condition->greaterThan !== null && $user->registrationDate >= TIME_NOW - $condition->greaterThan * 86400) {
			return false;
		}
		if ($condition->lessThan !== null && $user->registrationDate <= TIME_NOW - $condition->lessThan * 86400) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @see	\wcf\system\condition\AbstractIntegerCondition::getIdentifier()
	 */
	protected function getIdentifier() {
		return 'user_registrationDateInterval';
	}
	
	/**
	 * @see	\wcf\system\condition\IContentCondition::showContent()
	 */
	public function showContent(Condition $condition) {
		if (!WCF::getUser()->userID) return false;
		
		return $this->checkUser($condition, WCF::getUser());
	}
}
