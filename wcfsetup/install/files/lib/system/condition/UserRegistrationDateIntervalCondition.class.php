<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\data\DatabaseObjectList;
use wcf\system\WCF;

/**
 * Condition implementation for a relative interval for the registration date of
 * a user.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 */
class UserRegistrationDateIntervalCondition extends AbstractIntegerCondition implements IContentCondition, IObjectListCondition, IUserCondition {
	use TObjectListUserCondition;
	
	/**
	 * @inheritDoc
	 */
	protected $label = 'wcf.user.condition.registrationDateInterval';
	
	/**
	 * @inheritDoc
	 */
	protected $minValue = 0;
	
	/**
	 * @inheritDoc
	 */
	public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData) {
		if (!($objectList instanceof UserList)) {
			throw new \InvalidArgumentException("Object list is no instance of '".UserList::class."', instance of '".get_class($objectList)."' given.");
		}
		
		if ($conditionData['greaterThan'] !== null) {
			$objectList->getConditionBuilder()->add('user_table.registrationDate < ?', [TIME_NOW - $conditionData['greaterThan'] * 86400]);
		}
		if ($conditionData['lessThan'] !== null) {
			$objectList->getConditionBuilder()->add('user_table.registrationDate > ?', [TIME_NOW - $conditionData['lessThan'] * 86400]);
		}
	}
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	protected function getIdentifier() {
		return 'user_registrationDateInterval';
	}
	
	/**
	 * @inheritDoc
	 */
	public function showContent(Condition $condition) {
		if (!WCF::getUser()->userID) return false;
		
		return $this->checkUser($condition, WCF::getUser());
	}
}
