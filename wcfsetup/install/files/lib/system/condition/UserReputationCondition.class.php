<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\data\DatabaseObjectList;
use wcf\system\WCF;

/**
 * Condition implementation for the user reputation of an user.
 *
 * @author      Joshua Ruesweg
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Condition
 */
class UserReputationCondition extends AbstractIntegerCondition implements IContentCondition, IObjectListCondition, IUserCondition {
	use TObjectListUserCondition;
	
	/**
	 * @inheritDoc
	 */
	public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData) {
		if (!($objectList instanceof UserList)) {
			throw new \InvalidArgumentException("Object list is no instance of '".UserList::class."', instance of '".get_class($objectList)."' given.");
		}
		
		if (isset($conditionData['greaterThan'])) {
			/** @noinspection PhpUndefinedFieldInspection */
			$objectList->getConditionBuilder()->add('(user_table.positiveReactionsReceived -  user_table.negativeReactionsReceived) > ?', [$conditionData['greaterThan']]);
		}
		if (isset($conditionData['lessThan'])) {
			$objectList->getConditionBuilder()->add('(user_table.positiveReactionsReceived -  user_table.negativeReactionsReceived) < ?', [$conditionData['lessThan']]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkUser(Condition $condition, User $user) {
		if ($condition->greaterThan !== null && ($user->positiveReactionsReceived - $user->negativeReactionsReceived) <= $condition->greaterThan) {
			return false;
		}
		if ($condition->lessThan !== null && ($user->positiveReactionsReceived - $user->negativeReactionsReceived) >= $condition->lessThan) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getIdentifier() {
		return 'user_userReputation';
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getLabel() {
		return WCF::getLanguage()->get('wcf.user.condition.userReputation');
	}
	
	/**
	 * @inheritDoc
	 */
	public function showContent(Condition $condition) {
		if (!WCF::getUser()->userID) return false;
		
		return $this->checkUser($condition, WCF::getUser());
	}
}
