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
 * Condition implementation for an integer property of a user.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Condition
 */
class UserIntegerPropertyCondition extends AbstractIntegerCondition implements IContentCondition, IObjectCondition, IObjectListCondition {
	use TObjectListUserCondition;
	use TObjectUserCondition;
	
	/**
	 * @inheritDoc
	 */
	public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData) {
		if (!($objectList instanceof UserList)) {
			throw new \InvalidArgumentException("Object list is no instance of '".UserList::class."', instance of '".get_class($objectList)."' given.");
		}
		
		if (isset($conditionData['greaterThan'])) {
			/** @noinspection PhpUndefinedFieldInspection */
			$objectList->getConditionBuilder()->add('user_table.'.$this->getDecoratedObject()->propertyname.' > ?', [$conditionData['greaterThan']]);
		}
		if (isset($conditionData['lessThan'])) {
			$objectList->getConditionBuilder()->add('user_table.'.$this->getDecoratedObject()->propertyname.' < ?', [$conditionData['lessThan']]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkObject(DatabaseObject $object, array $conditionData) {
		if (!($object instanceof User) || ($object instanceof DatabaseObjectDecorator && !($object->getDecoratedObject() instanceof User))) {
			throw new \InvalidArgumentException("Object is no (decorated) instance of '".User::class."', instance of '".get_class($object)."' given.");
		}
		
		if ($conditionData['greaterThan'] !== null && $object->{$this->getDecoratedObject()->propertyname} <= $conditionData['greaterThan']) {
			return false;
		}
		if ($conditionData['lessThan'] !== null && $object->{$this->getDecoratedObject()->propertyname} >= $conditionData['lessThan']) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getIdentifier() {
		return 'user_'.$this->getDecoratedObject()->propertyname;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getLabel() {
		return WCF::getLanguage()->get('wcf.user.condition.'.$this->getDecoratedObject()->propertyname);
	}
	
	/**
	 * @inheritDoc
	 */
	public function showContent(Condition $condition) {
		if (!WCF::getUser()->userID) return false;
		
		return $this->checkObject(WCF::getUser(), $condition->conditionData);
	}
}
