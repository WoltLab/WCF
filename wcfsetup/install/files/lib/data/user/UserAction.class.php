<?php
namespace wcf\data\user;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\user\group\UserGroup;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\ValidateActionException;
use wcf\system\WCF;

/**
 * Executes user-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user
 * @category 	Community Framework
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
		foreach ($this->users as $user) $userIDs[] = $user->userID;
		
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
	 * 
	 * @todo	Handle multiple users?
	 */
	public function validateUpdate() {
		// read and validate user objects
		parent::validateUpdate();
		
		// editing own user
		if (count($this->objectIDs) == 1 && WCF::getUser()->userID == $this->objects[0]->userID) return;
		
		throw new ValidateActionException('Insufficient permissions');
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
}
