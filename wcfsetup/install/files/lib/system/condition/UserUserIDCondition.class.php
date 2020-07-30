<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\data\DatabaseObjectList;
use wcf\system\WCF;

/**
 * Condition implementation for the user id of a user.
 * 
 * @author      Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Condition
 */
class UserUserIDCondition extends AbstractSingleFieldCondition implements IContentCondition, IObjectListCondition, IUserCondition {
	use TObjectListUserCondition;
	
	/**
	 * @inheritDoc
	 */
	protected $label = 'wcf.user.userID';
	
	/**
	 * @var int|null
	 */
	protected $userID;
	
	/**
	 * @inheritDoc
	 */
	public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData) {
		if (!($objectList instanceof UserList)) {
			throw new \InvalidArgumentException("Object list is not an instance of '".UserList::class."', an instance of '".get_class($objectList)."' was given.");
		}
		
		$objectList->getConditionBuilder()->add('user_table.userID = ?', [$conditionData['userID']]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkUser(Condition $condition, User $user) {
		return $user->userID == $condition->userID;
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
	 */
	protected function getFieldElement() {
		return '<input type="number" name="userID" value="'.$this->userID.'" class="small">';
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		if (!empty($_POST['userID'])) $this->userID = intval($_POST['userID']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getData() {
		if ($this->userID !== null) {
			return ['userID' => $this->userID];
		}
		
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function setData(Condition $condition) {
		$this->userID = $condition->conditionData['userID'];
	}
}
