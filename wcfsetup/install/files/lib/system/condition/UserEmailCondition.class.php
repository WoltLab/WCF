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
 * Condition implementation for the email address of a user.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Condition
 */
class UserEmailCondition extends AbstractTextCondition implements IContentCondition, IObjectCondition, IObjectListCondition {
	use TObjectListUserCondition;
	use TObjectUserCondition;
	
	/**
	 * @inheritDoc
	 */
	protected $fieldName = 'email';
	
	/**
	 * @inheritDoc
	 */
	protected $label = 'wcf.user.email';
	
	/**
	 * @inheritDoc
	 */
	public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData) {
		if (!($objectList instanceof UserList)) {
			throw new \InvalidArgumentException("Object list is no instance of '".UserList::class."', instance of '".get_class($objectList)."' given.");
		}
		
		$objectList->getConditionBuilder()->add('user_table.email LIKE ?', ['%'.addcslashes($conditionData['email'], '_%').'%']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkObject(DatabaseObject $object, array $conditionData) {
		if (!($object instanceof User) || ($object instanceof DatabaseObjectDecorator && !($object->getDecoratedObject() instanceof User))) {
			throw new \InvalidArgumentException("Object is no (decorated) instance of '".User::class."', instance of '".get_class($object)."' given.");
		}
		
		return mb_strpos($object->email, $conditionData['email']) !== false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function showContent(Condition $condition) {
		if (!WCF::getUser()->userID) return false;
		
		return $this->checkObject(WCF::getUser(), $condition->conditionData);
	}
}
