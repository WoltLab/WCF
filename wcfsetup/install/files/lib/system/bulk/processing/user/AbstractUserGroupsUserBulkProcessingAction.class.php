<?php
namespace wcf\system\bulk\processing\user;
use wcf\data\user\group\UserGroup;
use wcf\data\user\UserEditor;
use wcf\data\user\UserList;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\system\exception\UserInputException;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Abstract implementation of a user bulk processing action related to selecting
 * user groups.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bulk\Processing\User
 * @since	3.0
 */
abstract class AbstractUserGroupsUserBulkProcessingAction extends AbstractUserBulkProcessingAction {
	/**
	 * list of available user groups
	 * @var	UserGroup[]
	 */
	public $availableUserGroups = [];
	
	/**
	 * name of the inputs used to store the selected user group ids
	 * @var	string
	 */
	public $inputName = '';
	
	/**
	 * ids of selected user groups
	 * @var	integer[]
	 */
	public $userGroupIDs = [];
	
	/**
	 * @inheritDoc
	 */
	public function __construct(DatabaseObject $object) {
		parent::__construct($object);
		
		$this->availableUserGroups = UserGroup::getAccessibleGroups([], [UserGroup::GUESTS, UserGroup::EVERYONE, UserGroup::USERS]);
		
		uasort($this->availableUserGroups, function(UserGroup $groupA, UserGroup $groupB) {
			return strcmp($groupA->getName(), $groupB->getName());
		});
	}
	
	/**
	 * @inheritDoc
	 */
	public function executeAction(DatabaseObjectList $objectList) {
		if (!($objectList instanceof UserList)) return;
		
		$users = $this->getAccessibleUsers($objectList);
		
		if (!empty($users)) {
			WCF::getDB()->beginTransaction();
			foreach ($users as $user) {
				$user = new UserEditor($user);
				$this->executeUserAction($user);
			}
			WCF::getDB()->commitTransaction();
			
			UserStorageHandler::getInstance()->reset(array_keys($users), 'groupIDs');
		}
	}
	
	/**
	 * Execute the action for the given user.
	 * 
	 * @param	\wcf\data\user\UserEditor	$user
	 */
	abstract protected function executeUserAction(UserEditor $user);
	
	/**
	 * @inheritDoc
	 */
	public function getHTML() {
		return WCF::getTPL()->fetch('userGroupListUserBulkProcessing', 'wcf', [
			'availableUserGroups' => $this->availableUserGroups,
			'inputName' => $this->inputName,
			'selectedUserGroupIDs' => $this->userGroupIDs
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function isAvailable() {
		return !empty($this->availableUserGroups);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		if (isset($_POST[$this->inputName])) $this->userGroupIDs = ArrayUtil::toIntegerArray($_POST[$this->inputName]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function reset() {
		$this->userGroupIDs = [];
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		if (empty($this->userGroupIDs)) {
			throw new UserInputException($this->inputName);
		}
		
		foreach ($this->userGroupIDs as $groupID) {
			if (!isset($this->availableUserGroups[$groupID])) {
				throw new UserInputException($this->inputName, 'noValidSelection');
			}
		}
	}
}
