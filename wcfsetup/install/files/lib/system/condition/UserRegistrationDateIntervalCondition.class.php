<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\DatabaseObjectList;
use wcf\system\WCF;

/**
 * Condition implementation for a relative interval for the registration date of
 * a user.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Condition
 */
class UserRegistrationDateIntervalCondition extends AbstractIntegerCondition implements IContentCondition, IObjectCondition, IObjectListCondition {
	use TObjectListUserCondition;
	use TObjectUserCondition;
	
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
	public function checkObject(DatabaseObject $object, array $conditionData) {
		if (!($object instanceof User) || ($object instanceof DatabaseObjectDecorator && !($object->getDecoratedObject() instanceof User))) {
			throw new \InvalidArgumentException("Object is no (decorated) instance of '".User::class."', instance of '".get_class($object)."' given.");
		}
		
		$greaterThan = $conditionData['greaterThan'];
		if ($greaterThan !== null && $object->registrationDate >= TIME_NOW - $greaterThan * 86400) {
			return false;
		}
		
		$lessThan = $conditionData['lessThan'];
		if ($lessThan !== null && $object->registrationDate <= TIME_NOW - $lessThan * 86400) {
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
		
		return $this->checkObject(WCF::getUser(), $condition->conditionData);
	}
}
