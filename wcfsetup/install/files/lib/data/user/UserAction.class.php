<?php
namespace wcf\data\user;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\avatar\UserAvatarAction;
use wcf\data\user\group\UserGroup;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IClipboardAction;
use wcf\data\ISearchAction;
use wcf\system\cache\builder\UserNotificationEventCacheBuilder;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\StringUtil;
use wcf\util\UserRegistrationUtil;

/**
 * Executes user-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user
 * @category	Community Framework
 */
class UserAction extends AbstractDatabaseObjectAction implements IClipboardAction, ISearchAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	public $className = 'wcf\data\user\UserEditor';
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$allowGuestAccess
	 */
	protected $allowGuestAccess = array('getSearchResultList');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsCreate
	 */
	protected $permissionsCreate = array('admin.user.canAddUser');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.user.canDeleteUser');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.user.canEditUser');
	
	/**
	 * Validates permissions and parameters.
	 */
	public function validateCreate() {
		$this->readString('password', false, 'data');
	}
	
	/**
	 * Validates accessible groups.
	 * 
	 * @param	boolean		$ignoreOwnUser
	 */
	protected function __validateAccessibleGroups($ignoreOwnUser = true) {
		if ($ignoreOwnUser) {
			if (in_array(WCF::getUser()->userID, $this->objectIDs)) {
				unset($this->objectIDs[array_search(WCF::getUser()->userID, $this->objectIDs)]);
				if (isset($this->objects[WCF::getUser()->userID])) {
					unset($this->objects[WCF::getUser()->userID]);
				}
			}
		}
		
		// list might be empty because only our own user id was given
		if (empty($this->objectIDs)) {
			throw new UserInputException('objectIDs');
		}
		
		// validate groups
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", array($this->objectIDs));
		
		$sql = "SELECT	DISTINCT groupID
			FROM	wcf".WCF_N."_user_to_group
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		$groupIDs = array();
		while ($row = $statement->fetchArray()) {
			$groupIDs[] = $row['groupID'];
		}
		
		if (!UserGroup::isAccessibleGroup($groupIDs)) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Validates permissions and parameters.
	 */
	public function validateDelete() {
		// read and validate user objects
		parent::validateDelete();
		
		$this->__validateAccessibleGroups();
	}
	
	/**
	 * @see	wcf\data\IDeleteAction::delete()
	 */
	public function delete() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		// delete avatars
		$avatarIDs = array();
		foreach ($this->objects as $user) {
			if ($user->avatarID) $avatarIDs[] = $user->avatarID;
		}
		if (!empty($avatarIDs)) {
			$action = new UserAvatarAction($avatarIDs, 'delete');
			$action->executeAction();
		}
		
		// delete profile comments
		if (!empty($this->objectIDs)) {
			$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.comment.commentableContent', 'com.woltlab.wcf.user.profileComment');
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add('objectTypeID = ?', array($objectType->objectTypeID));
			$conditionBuilder->add('objectID IN (?)', array($this->objectIDs));
			
			$sql = "DELETE FROM	wcf".WCF_N."_comment
				".$conditionBuilder;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditionBuilder->getParameters());
		}
		
		$returnValue = parent::delete();
		
		return $returnValue;
	}
	
	/**
	 * Validates permissions and parameters.
	 */
	public function validateUpdate() {
		// read objects
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
		
		try {
			WCF::getSession()->checkPermissions($this->permissionsUpdate);
		}
		catch (PermissionDeniedException $e) {
			// check if we're editing ourselves
			if (count($this->objects) == 1 && ($this->objects[0]->userID == WCF::getUser()->userID)) {
				$count = count($this->parameters);
				if ($count > 1 || ($count == 1 && !isset($this->parameters['options']))) {
					throw new PermissionDeniedException();
				}
			}
			
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Validates the ban action.
	 */
	public function validateBan() {
		WCF::getSession()->checkPermissions(array('admin.user.canBanUser'));
		
		$this->__validateAccessibleGroups();
	}
	
	/**
	 * Validates the unban action.
	 */
	public function validateUnban() {
		$this->validateBan();
	}
	
	/**
	 * Bans users.
	 */
	public function ban() {
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('userID IN (?)', array($this->objectIDs));
		$sql = "UPDATE	wcf".WCF_N."_user
			SET	banned = ?,
				banReason = ?
			".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(
			array_merge(array(1, $this->parameters['banReason']), $conditionBuilder->getParameters())		
		);
		
		$this->unmarkItems();
	}
	
	/**
	 * Unbans users.
	 */
	public function unban() {
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('userID IN (?)', array($this->objectIDs));
		$sql = "UPDATE	wcf".WCF_N."_user
			SET	banned = 0
			".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
	}
	
	/**
	 * Creates a new user.
	 * 
	 * @return	User
	 */
	public function create() {
		$user = parent::create();
		$userEditor = new UserEditor($user);
		
		// updates user options
		if (isset($this->parameters['options'])) {
			$userEditor->updateUserOptions($this->parameters['options']);
		}
		
		// insert user groups
		$addDefaultGroups = (isset($this->parameters['addDefaultGroups'])) ? $this->parameters['addDefaultGroups'] : true;
		$groupIDs = (isset($this->parameters['groups'])) ? $this->parameters['groups'] : array();
		$userEditor->addToGroups($groupIDs, false, $addDefaultGroups);
		
		// insert visible languages
		$languageIDs = (isset($this->parameters['languages'])) ? $this->parameters['languages'] : array();
		$userEditor->addToLanguages($languageIDs, false);
		
		if (PACKAGE_ID) {
			// set default notifications
			$sql = "INSERT INTO	wcf".WCF_N."_user_notification_event_to_user
						(userID, eventID)
				SELECT		?, eventID
				FROM		wcf".WCF_N."_user_notification_event
				WHERE		preset = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($user->userID, 1));
		}
		
		return $user;
	}
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::update()
	 */
	public function update() {
		if (isset($this->parameters['data'])) {
			parent::update();
			
			if (isset($this->parameters['data']['languageID'])) {
				foreach ($this->objects as $object) {
					if ($object->userID == WCF::getUser()->userID) {
						if ($this->parameters['data']['languageID'] != WCF::getUser()->languageID) {
							WCF::setLanguage($this->parameters['data']['languageID']);
						}
						
						break;
					}
				}
			}
		}
		else {
			if (empty($this->objects)) {
				$this->readObjects();
			}
		}
		
		$groupIDs = (isset($this->parameters['groups'])) ? $this->parameters['groups'] : array();
		$languageIDs = (isset($this->parameters['languageIDs'])) ? $this->parameters['languageIDs'] : array();
		$removeGroups = (isset($this->parameters['removeGroups'])) ? $this->parameters['removeGroups'] : array();
		$userOptions = (isset($this->parameters['options'])) ? $this->parameters['options'] : array();
		
		if (!empty($groupIDs)) {
			$action = new UserAction($this->objects, 'addToGroups', array(
				'groups' => $groupIDs,
				'addDefaultGroups' => false
			));
			$action->executeAction();
		}
		
		foreach ($this->objects as $userEditor) {
			if (!empty($removeGroups)) {
				$userEditor->removeFromGroups($removeGroups);
			}
			
			if (!empty($userOptions)) {
				$userEditor->updateUserOptions($userOptions);
			}
			
			if (!empty($languageIDs)) {
				$userEditor->addToLanguages($languageIDs);
			}
		}
	}
	
	/**
	 * Add users to given groups.
	 */
	public function addToGroups() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		$groupIDs = $this->parameters['groups'];
		$deleteOldGroups = $addDefaultGroups = true;
		if (isset($this->parameters['deleteOldGroups'])) $deleteOldGroups = $this->parameters['deleteOldGroups'];
		if (isset($this->parameters['addDefaultGroups'])) $addDefaultGroups = $this->parameters['addDefaultGroups'];
		
		foreach ($this->objects as $userEditor) {
			$userEditor->addToGroups($groupIDs, $deleteOldGroups, $addDefaultGroups);
		}
		
		if (MODULE_USER_RANK) {
			$action = new UserProfileAction($this->objects, 'updateUserRank');
			$action->executeAction();
		}
		if (MODULE_USERS_ONLINE) {
			$action = new UserProfileAction($this->objects, 'updateUserOnlineMarking');
			$action->executeAction();
		}
	}
	
	/**
	 * @see	wcf\data\ISearchAction::validateGetSearchResultList()
	 */
	public function validateGetSearchResultList() {
		$this->readBoolean('includeUserGroups', false, 'data');
		$this->readString('searchString', false, 'data');
		
		if (isset($this->parameters['data']['excludedSearchValues']) && !is_array($this->parameters['data']['excludedSearchValues'])) {
			throw new UserInputException('excludedSearchValues');
		}
	}
	
	/**
	 * @see	wcf\data\ISearchAction::getSearchResultList()
	 */
	public function getSearchResultList() {
		$searchString = $this->parameters['data']['searchString'];
		$excludedSearchValues = array();
		if (isset($this->parameters['data']['excludedSearchValues'])) {
			$excludedSearchValues = $this->parameters['data']['excludedSearchValues'];
		}
		$list = array();
		
		if ($this->parameters['data']['includeUserGroups']) {
			$accessibleGroups = UserGroup::getAccessibleGroups();
			foreach ($accessibleGroups as $group) {
				$groupName = $group->getName();
				if (!in_array($groupName, $excludedSearchValues)) {
					$pos = mb_strripos($groupName, $searchString);
					if ($pos !== false && $pos == 0) {
						$list[] = array(
							'label' => $groupName,
							'objectID' => $group->groupID,
							'type' => 'group'
						);
					}
				}
			}
		}
		
		// find users
		$userProfileList = new UserProfileList();
		$userProfileList->getConditionBuilder()->add("username LIKE ?", array($searchString.'%'));
		if (!empty($excludedSearchValues)) {
			$userProfileList->getConditionBuilder()->add("username NOT IN (?)", array($excludedSearchValues));
		}
		$userProfileList->sqlLimit = 10;
		$userProfileList->readObjects();
		
		foreach ($userProfileList as $userProfile) {
			$list[] = array(
				'icon' => $userProfile->getAvatar()->getImageTag(16),
				'label' => $userProfile->username,
				'objectID' => $userProfile->userID,
				'type' => 'user'
			);
		}
		
		return $list;
	}
	
	/**
	 * @see	wcf\data\IClipboardAction::validateUnmarkAll()
	 */
	public function validateUnmarkAll() {
		// does nothing
	}
	
	/**
	 * @see	wcf\data\IClipboardAction::unmarkAll()
	 */
	public function unmarkAll() {
		ClipboardHandler::getInstance()->removeItems(ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user'));
	}
	
	/**
	 * Unmarks users.
	 *
	 * @param	array<integer>		$userIDs
	 */
	protected function unmarkItems(array $userIDs = array()) {
		if (empty($userIDs)) {
			$userIDs = $this->objectIDs;
		}
	
		if (!empty($userIDs)) {
			ClipboardHandler::getInstance()->unmark($userIDs, ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user'));
		}
	}
	
	/**
	 * Validates the enable action.
	 */
	public function validateEnable() {
		WCF::getSession()->checkPermissions(array('admin.user.canEnableUser'));
	}
	
	/**
	 * Validates the disable action.
	 */
	public function validateDisable() {
		$this->validateEnable();
	}
	
	/**
	 * Enables users.
	 */
	public function enable() {
		if (empty($this->objects)) $this->readObjects();
	
		$action = new UserAction($this->objects, 'update', array(
			'data' => array(
				'activationCode' => 0
			),
			'removeGroups' => UserGroup::getGroupIDsByType(array(UserGroup::GUESTS))
		));
		$action->executeAction();
		$action = new UserAction($this->objects, 'addToGroups', array(
			'groups' => UserGroup::getGroupIDsByType(array(UserGroup::USERS)),
			'deleteOldGroups' => false,
			'addDefaultGroups' => false	
		));
		$action->executeAction();
	}
	
	/**
	 * Disables users.
	 */
	public function disable() {
		if (empty($this->objects)) $this->readObjects();
	
		$action = new UserAction($this->objects, 'update', array(
			'data' => array(
				'activationCode' => UserRegistrationUtil::getActivationCode()
			),
			'removeGroups' => UserGroup::getGroupIDsByType(array(UserGroup::USERS)),
		));
		$action->executeAction();
		$action = new UserAction($this->objects, 'addToGroups', array(
			'groups' => UserGroup::getGroupIDsByType(array(UserGroup::GUESTS)),
			'deleteOldGroups' => false,
			'addDefaultGroups' => false
		));
		$action->executeAction();
	}
}
