<?php
namespace wcf\data\user;
use wcf\data\user\group\UserGroup;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\ValidateActionException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Executes user-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user
 * @category	Community Framework
 */
class UserAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	public $className = 'wcf\data\user\UserEditor';
	
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
		if (!isset($this->parameters['data']['password'])) {
			throw new ValidateActionException("Missing parameter 'password'");
		}
	}
	
	/**
	 * Validates permissions and parameters.
	 */
	public function validateDelete() {
		// read and validate user objects
		parent::validateDelete();
		
		$userIDs = array();
		foreach ($this->objects as $user) {
			// you cannot delete yourself
			if ($user->userID == WCF::getUser()->userID) {
				continue;
			}
			
			$userIDs[] = $user->userID;
		}
		
		// list might be empty because only our own user id was given
		if (empty($userIDs)) {
			throw new ValidateActionException("Invalid object id");
		}
		
		// validate groups
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", array($userIDs));
		
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
			throw new ValidateActionException('Insufficient permissions');
		}
	}
	
	/**
	 * Validates permissions and parameters.
	 */
	public function validateUpdate() {
		// read objects
		if (!count($this->objects)) {
			$this->readObjects();
		}
		
		if (!count($this->objects)) {
			throw new ValidateActionException('Invalid object id');
		}
		
		try {
			WCF::getSession()->checkPermissions($this->permissionsUpdate);
		}
		catch (PermissionDeniedException $e) {
			// check if we're editing ourselves
			if (count($this->objects) == 1 && ($this->objects[0]->userID == WCF::getUser()->userID)) {
				$count = count($this->parameters);
				if ($count > 1 || ($count == 1 && !isset($this->parameters['options']))) {
					throw new ValidateActionException('Insufficient permissions');
				}
			}
			
			throw new ValidateActionException('Insufficient permissions');
		}
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
		$userEditor->addToLanguages($languageIDs);
		
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
			if (!count($this->objects)) {
				$this->readObjects();
			}
		}
		
		$groupIDs = (isset($this->parameters['groups'])) ? $this->parameters['groups'] : array();
		$languageIDs = (isset($this->parameters['languageIDs'])) ? $this->parameters['languageIDs'] : array();
		$removeGroups = (isset($this->parameters['removeGroups'])) ? $this->parameters['removeGroups'] : array();
		$userOptions = (isset($this->parameters['options'])) ? $this->parameters['options'] : array();
		
		foreach ($this->objects as $userEditor) {
			if (!empty($groupIDs)) {
				$userEditor->addToGroups($groupIDs);
			}
			
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
	 * Validates parameters to search for users and -groups.
	 */
	public function validateGetList() {
		if (!isset($this->parameters['data']['searchString'])) {
			throw new ValidateActionException("Missing parameter 'searchString'");
		}
		
		if (!isset($this->parameters['data']['includeUserGroups'])) {
			throw new ValidateActionException("Missing parameter 'includeUserGroups'");
		}
		
		if (isset($this->parameters['data']['excludedSearchValues']) && !is_array($this->parameters['data']['excludedSearchValues'])) {
			throw new ValidateActionException("Invalid parameter 'excludedSearchValues' given");
		}
	}
	
	/**
	 * Returns a list of users and -groups based upon given search criteria.
	 * 
	 * @return	array<array>
	 */
	public function getList() {
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
					$pos = StringUtil::indexOfIgnoreCase($groupName, $searchString);
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
		
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add("username LIKE ?", array($searchString.'%'));
		if (count($excludedSearchValues)) {
			$conditionBuilder->add("username NOT IN (?)", array($excludedSearchValues));
		}
		
		// find users
		$sql = "SELECT	userID, username
			FROM	wcf".WCF_N."_user
			".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql, 10); /* TODO: add limit parameter */
		$statement->execute($conditionBuilder->getParameters());
		while ($row = $statement->fetchArray()) {
			$list[] = array(
				'label' => $row['username'],
				'objectID' => $row['userID'],
				'type' => 'user'
			);
		}
		
		return $list;
	}
}
