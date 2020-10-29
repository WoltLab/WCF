<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\data\DatabaseObjectList;
use wcf\system\exception\InvalidObjectArgument;
use wcf\system\WCF;

/**
 * Condition implementation for an integer property of a user.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Condition
 */
class UserIntegerPropertyCondition extends AbstractIntegerCondition implements IContentCondition, IObjectListCondition, IUserCondition {
	use TObjectListUserCondition;
	
	/**
	 * @inheritDoc
	 */
	public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData) {
		if (!($objectList instanceof UserList)) {
			throw new InvalidObjectArgument($objectList, UserList::class, 'Object list');
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
	public function checkUser(Condition $condition, User $user) {
		if ($condition->greaterThan !== null && $user->{$this->getDecoratedObject()->propertyname} <= $condition->greaterThan) {
			return false;
		}
		if ($condition->lessThan !== null && $user->{$this->getDecoratedObject()->propertyname} >= $condition->lessThan) {
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
		
		return $this->checkUser($condition, WCF::getUser());
	}
}
