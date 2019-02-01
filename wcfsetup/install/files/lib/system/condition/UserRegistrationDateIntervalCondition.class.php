<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\data\DatabaseObjectList;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Condition implementation for a relative interval for the registration date of
 * a user.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Condition
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
		
		if (isset($conditionData['greaterThan'])) {
			$objectList->getConditionBuilder()->add('user_table.registrationDate < ?', [TIME_NOW - $conditionData['greaterThan'] * 86400]);
		}
		if (isset($conditionData['lessThan'])) {
			$objectList->getConditionBuilder()->add('user_table.registrationDate > ?', [TIME_NOW - $conditionData['lessThan'] * 86400]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkUser(Condition $condition, User $user) {
		/** @noinspection PhpUndefinedFieldInspection */
		$greaterThan = $condition->greaterThan;
		if ($greaterThan !== null && $user->registrationDate >= TIME_NOW - $greaterThan * 86400) {
			return false;
		}
		
		/** @noinspection PhpUndefinedFieldInspection */
		$lessThan = $condition->lessThan;
		if ($lessThan !== null && $user->registrationDate <= TIME_NOW - $lessThan * 86400) {
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
	
	/**
	 * @inheritDoc
	 * 
	 * @since	3.0
	 */
	protected function validateConflictingValues() {
		if ($this->lessThan !== null && $this->greaterThan !== null && $this->greaterThan >= $this->lessThan) {
			$this->errorMessage = 'wcf.condition.greaterThan.error.lessThan';
			
			throw new UserInputException('greaterThan', 'lessThan');
		}
	}
}
