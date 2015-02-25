<?php
namespace wcf\acp\form;
use wcf\data\user\group\option\category\UserGroupOptionCategoryList;
use wcf\data\user\group\option\UserGroupOption;
use wcf\data\user\group\option\UserGroupOptionAction;
use wcf\data\user\group\UserGroup;
use wcf\data\DatabaseObject;
use wcf\form\AbstractForm;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Shows the user group option form to edit a single option.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class UserGroupOptionForm extends AbstractForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.group';
	
	/**
	 * list of parsed form elements per group
	 * @var	array<string>
	 */
	public $formElements = array();
	
	/**
	 * list of accessible groups
	 * @var	array<\wcf\data\user\group\UserGroup>
	 */
	public $groups = array();
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.user.canEditGroup');
	
	/**
	 * user group option type object
	 * @var	\wcf\system\option\user\group\IUserGroupOptionType
	 */
	public $optionType = null;
	
	/**
	 * list of parent categories
	 * @var	array<\wcf\data\user\group\option\category\UserGroupOptionCategory>
	 */
	public $parentCategories = array();
	
	/**
	 * list of values per user group
	 * @var	array
	 */
	public $values = array();
	
	/**
	 * user group option object
	 * @var	\wcf\data\user\group\option\UserGroupOption
	 */
	public $userGroupOption = null;
	
	/**
	 * user group option id
	 * @var	integer
	 */
	public $userGroupOptionID = 0;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->userGroupOptionID = intval($_REQUEST['id']);
		$this->userGroupOption = new UserGroupOption($this->userGroupOptionID);
		if (!$this->userGroupOption) {
			throw new IllegalLinkException();
		}
		
		// verify options and permissions for current option
		if ($this->verifyPermissions($this->userGroupOption)) {
			// read all categories
			$categoryList = new UserGroupOptionCategoryList();
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
				
				array_unshift($this->parentCategories, $category);
				$category = ($category->parentCategoryName != '') ? $categories[$category->parentCategoryName] : null;
			}
		}
		else {
			throw new PermissionDeniedException();
		}
		
		// read accessible groups
		$this->groups = UserGroup::getAccessibleGroups();
		if ($this->userGroupOption->usersOnly) {
			$guestGroup = UserGroup::getGroupByType(UserGroup::GUESTS);
			if (isset($this->groups[$guestGroup->groupID])) {
				unset($this->groups[$guestGroup->groupID]);
			}
		}
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
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['values']) && is_array($_POST['values'])) $this->values = $_POST['values'];
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		$isAdmin = false;
		foreach (WCF::getUser()->getGroupIDs() as $groupID) {
			if (UserGroup::getGroupByID($groupID)->isAdminGroup()) {
				$isAdmin = true;
				break;
			}
		}
		
		// validate option values
		foreach ($this->values as $groupID => &$optionValue) {
			if (!isset($this->groups[$groupID])) {
				throw new PermissionDeniedException();
			}
			
			$optionValue = $this->optionType->getData($this->userGroupOption, $optionValue);
			
			try {
				$this->optionType->validate($this->userGroupOption, $optionValue);
			}
			catch (UserInputException $e) {
				$this->errorType[$e->getField()] = $e->getType();
			}
			
			if (!$isAdmin && $this->optionType->compare($optionValue, WCF::getSession()->getPermission($this->userGroupOption->optionName)) == 1) {
				$this->errorType[$groupID] = 'exceedsOwnPermission';
			}
		}
		
		// add missing values for option type 'boolean'
		if ($this->userGroupOption->optionType == 'boolean') {
			foreach ($this->groups as $groupID => $group) {
				if (!isset($this->values[$groupID])) {
					$this->values[$groupID] = 0;
				}
			}
		}
		
		if (!empty($this->errorType)) {
			throw new UserInputException('optionValues', $this->errorType);
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			// read values of accessible user groups
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("groupID IN (?)", array(array_keys($this->groups)));
			$conditions->add("optionID = ?", array($this->userGroupOption->optionID));
			
			$sql = "SELECT	groupID, optionValue
				FROM	wcf".WCF_N."_user_group_option_value
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			while ($row = $statement->fetchArray()) {
				$this->values[$row['groupID']] = $row['optionValue'];
			}
		}
		
		// create form elements for each group
		foreach ($this->groups as $group) {
			$optionValue = (isset($this->values[$group->groupID])) ? $this->values[$group->groupID] : $this->userGroupOption->defaultValue;
			$this->formElements[$group->groupID] = $this->optionType->getFormElement($this->userGroupOption, $optionValue);
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
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
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'formElements' => $this->formElements,
			'groups' => $this->groups,
			'parentCategories' => $this->parentCategories,
			'userGroupOption' => $this->userGroupOption,
			'values' => $this->values
		));
	}
	
	/**
	 * Validates object options and permissions.
	 * 
	 * @param	\wcf\data\DatabaseObject		$object
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
