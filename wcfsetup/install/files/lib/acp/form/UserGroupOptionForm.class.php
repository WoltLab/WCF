<?php
namespace wcf\acp\form;
use wcf\data\user\group\option\UserGroupOptionAction;

use wcf\data\user\group\option\category\UserGroupOptionCategoryList;
use wcf\data\user\group\option\UserGroupOption;
use wcf\data\user\group\UserGroup;
use wcf\data\DatabaseObject;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\package\PackageDependencyHandler;
use wcf\system\WCF;

/**
 * Shows the user group option form to edit a single option.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class UserGroupOptionForm extends ACPForm {
	/**
	 * @see	wcf\acp\form\ACPForm::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.group';
	
	/**
	 * true, if user can edit the 'everyone' group
	 * @var	boolean
	 */
	public $canEditEveryone = false;
	
	/**
	 * form element for default value
	 * @var	string
	 */
	public $defaultFormElement = '';
	
	/**
	 * default value for every group
	 * @var	mixed
	 */
	public $defaultValue = null;
	
	/**
	 * list of parsed form elements per group
	 * @var	array<string>
	 */
	public $formElements = array();
	
	/**
	 * list of accessible groups
	 * @var	array<wcf\data\user\group\UserGroup>
	 */
	public $groups = array();
	
	/**
	 * 'Everyone' user group object
	 * @var	wcf\data\user\group\UserGroup
	 */
	public $groupEveryone = null;
	
	/**
	 * @see	wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.user.canEditGroup');
	
	/**
	 * user group option type object
	 * @var	wcf\system\option\user\group\IUserGroupOptionType
	 */
	public $optionType = null;
	
	/**
	 * list of values per user group
	 * @var	array
	 */
	public $values = array();
	
	/**
	 * user group option object
	 * @var	wcf\data\user\group\option\UserGroupOption
	 */
	public $userGroupOption = null;
	
	/**
	 * user group option id
	 * @var	integer
	 */
	public $userGroupOptionID = 0;
	
	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->userGroupOptionID = intval($_REQUEST['id']);
		$this->userGroupOption = new UserGroupOption($this->userGroupOptionID);
		if (!$this->userGroupOption) {
			throw new IllegalLinkException();
		}
		
		// verify options and permissions for current option
		$dependencies = PackageDependencyHandler::getInstance()->getDependencies();
		if ($this->verifyPermissions($this->userGroupOption) && in_array($this->userGroupOption->packageID, $dependencies)) {
			// read all categories
			$categoryList = new UserGroupOptionCategoryList();
			$categoryList->getConditionBuilder()->add("packageID IN (?)", array($dependencies));
			$categoryList->sqlLimit = 0;
			$categoryList->readObjects();
			
			$categories = array();
			foreach ($categoryList as $category) {
				$categories[$category->categoryName] = $category;
			}
			
			// verify categories
			$category = $categories[$this->userGroupOption->categoryName];
			while ($category != null) {
				if (!$this->verifyPermissions($category)) {
					throw new PermissionDeniedException();
				}
				
				$category = ($category->parentCategoryName != '') ? $categories[$category->parentCategoryName] : null;
			}
		}
		else {
			throw new PermissionDeniedException();
		}
		
		// validate accessible groups
		$this->readAccessibleGroups();
		if (empty($this->groups)) {
			throw new PermissionDeniedException();
		}
		
		// get option type
		$className = 'wcf\system\option\user\group\\'.ucfirst($this->userGroupOption->optionType).'UserGroupOptionType';
		if (!class_exists($className)) {
			throw new SystemException("Unable to find option type for '".$this->userGroupOption->optionType."'");
		}
		$this->optionType = new $className();
	}
	
	/**
	 * @see	wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (isset($_POST['values']) && is_array($_POST['values'])) $this->values = $_POST['values'];
		if (empty($this->values)) {
			throw new IllegalLinkException();
		}
		
		if (!$this->canEditEveryone) {
			$sql = "SELECT	optionValue
				FROM	wcf".WCF_N."_user_group_option_value
				WHERE	optionID = ?
					AND groupID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				$this->userGroupOption->optionID,
				$this->groupEveryone->groupID
			));
			$row = $statement->fetchArray();
			$this->defaultValue = $row['optionValue'];
		}
		else {
			if (!isset($this->values[$this->groupEveryone->groupID])) {
				throw new IllegalLinkException();
			}
			
			$this->defaultValue = $this->values[$this->groupEveryone->groupID];
		}
		
		foreach ($this->values as $groupID => $optionValue) {
			if (!isset($this->groups[$groupID])) {
				if ($groupID == $this->groupEveryone->groupID) {
					if (!$this->canEditEveryone) {
						throw new PermissionDeniedException();
					}
				}
				else {
					throw new PermissionDeniedException();
				}
			}
			
			try {
				$this->optionType->validate($this->userGroupOption, $optionValue);
			}
			catch (UserInputException $e) {
				$this->errorType[$e->getField()] = $e->getType();
			}
			
			// check if not editing default value
			if ($groupID != $this->groupEveryone->groupID) {
				$newValue = $this->optionType->merge($this->defaultValue, $optionValue);
				if ($newValue === null) {
					unset($this->values[$groupID]);
				}
				else {
					$this->values[$groupID] = $newValue;
				}
			}
		}
		
		if (!empty($this->errorType)) {
			throw new UserInputException('optionValues', $this->errorType);
		}
	}
	
	/**
	 * @see	wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			// read values for accessible user groups
			$groupIDs = array_merge(array_keys($this->groups), array($this->groupEveryone->groupID));
			
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("groupID IN (?)", array($groupIDs));
			$conditions->add("optionID = ?", array($this->userGroupOption->optionID));
			
			$sql = "SELECT	groupID, optionValue
				FROM	wcf".WCF_N."_user_group_option_value
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			while ($row = $statement->fetchArray()) {
				// exclude default value from $values
				if ($row['groupID'] == $this->groupEveryone->groupID) {
					$this->defaultValue = $row['optionValue'];
					continue;
				}
				
				$this->values[$row['groupID']] = $row['optionValue'];
			}
		}
		
		// create form element for default group
		$this->defaultFormElement = $this->optionType->getFormElement($this->userGroupOption, $this->defaultValue);
		
		// create form elements for each group
		foreach ($this->groups as $group) {
			$optionValue = (isset($this->values[$group->groupID])) ? $this->values[$group->groupID] : $this->defaultValue;
			$this->formElements[$group->groupID] = $this->optionType->getFormElement($this->userGroupOption, $optionValue);
		}
	}
	
	/**
	 * @see	wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		$this->objectAction = new UserGroupOptionAction(array($this->userGroupOption), 'updateValues', array('values' => $this->values));
		$this->objectAction->executeAction();
		
		// fire saved event
		$this->saved();
		
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'canEditEveryone' => $this->canEditEveryone,
			'defaultFormElement' => $this->defaultFormElement,
			'defaultValue' => $this->defaultValue,
			'formElements' => $this->formElements,
			'groupEveryone' => $this->groupEveryone,
			'groups' => $this->groups,
			'userGroupOption' => $this->userGroupOption,
			'values' => $this->values
		));
	}
	
	/**
	 * Reads accessible user groups.
	 */
	protected function readAccessibleGroups() {
		$this->groups = UserGroup::getAccessibleGroups();
		$this->canEditEveryone = false;
		foreach ($this->groups as $groupID => $group) {
			if ($group->groupType == UserGroup::EVERYONE) {
				$this->canEditEveryone = true;
					
				// remove 'Everyone' from groups
				$this->groupEveryone = $group;
				unset($this->groups[$groupID]);
			}
		}
			
		// add 'Everyone' group
		if (!$this->canEditEveryone) {
			$this->groupEveryone = UserGroup::getGroupByType(UserGroup::EVERYONE);
		}
	}
	
	/**
	 * Validates object options and permissions.
	 *
	 * @param	wcf\data\DatabaseObject		$object
	 * @return	boolean
	 */
	protected function verifyPermissions(DatabaseObject $object) {
		// check the options of this item
		$hasEnabledOption = true;
		if ($object->options) {
			$hasEnabledOption = false;
			$options = explode(',', strtoupper($object->options));
			foreach ($options as $option) {
				if (defined($option) && constant($option)) {
					$hasEnabledOption = true;
					break;
				}
			}
		}
		if (!$hasEnabledOption) return false;
	
		// check the permission of this item for the active user
		$hasPermission = true;
		if ($object->permissions) {
			$hasPermission = false;
			$permissions = explode(',', $object->permissions);
			foreach ($permissions as $permission) {
				if (WCF::getSession()->getPermission($permission)) {
					$hasPermission = true;
					break;
				}
			}
		}
		if (!$hasPermission) return false;
	
		return true;
	}
}
