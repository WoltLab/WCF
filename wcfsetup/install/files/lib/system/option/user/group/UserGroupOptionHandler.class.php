<?php
namespace wcf\system\option\user\group;
use wcf\data\option\Option;
use wcf\data\user\group\UserGroup;
use wcf\system\cache\builder\UserGroupOptionCacheBuilder;
use wcf\system\exception\ImplementationException;
use wcf\system\exception\UserInputException;
use wcf\system\option\OptionHandler;
use wcf\system\WCF;

/**
 * Handles user group options.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option\User\Group
 */
class UserGroupOptionHandler extends OptionHandler {
	/**
	 * @inheritDoc
	 */
	protected $cacheClass = UserGroupOptionCacheBuilder::class;
	
	/**
	 * user group object
	 * @var	\wcf\data\user\group\UserGroup
	 */
	protected $group = null;
	
	/**
	 * true if current user can edit every user group
	 * @var	boolean
	 */
	protected $isAdmin = null;
	
	/**
	 * Sets current user group.
	 * 
	 * @param	\wcf\data\user\group\UserGroup	$group
	 */
	public function setUserGroup(UserGroup $group) {
		$this->group = $group;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTypeObject($type) {
		$objectType = parent::getTypeObject($type);
		
		if ($this->group !== null && $objectType instanceof IUserGroupGroupOptionType) {
			$objectType->setUserGroup($this->group);
		}
		
		return $objectType;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function checkOption(Option $option) {
		if (parent::checkOption($option)) {
			// check if permission is available for guests if group is guests
			if ($this->group && $this->group->groupType == UserGroup::GUESTS && $option->usersOnly) {
				return false;
			}
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getClassName($type) {
		$className = 'wcf\system\option\user\group\\'.ucfirst($type).'UserGroupOptionType';
		
		// validate class
		if (!class_exists($className)) {
			return null;
		}
		if (!is_subclass_of($className, IUserGroupOptionType::class)) {
			throw new ImplementationException($className, IUserGroupOptionType::class);
		}
		
		return $className;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		$defaultGroup = UserGroup::getGroupByType(UserGroup::EVERYONE);
		foreach ($this->options as $option) {
			$this->optionValues[$option->optionName] = $defaultGroup->getGroupOption($option->optionName);
			
			// use group values over default values
			if ($this->group !== null) {
				$groupValue = $this->group->getGroupOption($option->optionName);
				if ($groupValue !== null) {
					$this->optionValues[$option->optionName] = $groupValue;
				}
			}
		}
	}
	
	/**
	 * Returns true if current user has the permissions to edit every user group.
	 * 
	 * @return	boolean
	 */
	protected function isAdmin() {
		if ($this->isAdmin === null) {
			$this->isAdmin = false;
			
			foreach (WCF::getUser()->getGroupIDs() as $groupID) {
				if (UserGroup::getGroupByID($groupID)->isAdminGroup()) {
					$this->isAdmin = true;
					break;
				}
			}
		}
		
		return $this->isAdmin;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validateOption(Option $option) {
		parent::validateOption($option);
		
		if (!$this->isAdmin()) {
			// get type object
			$typeObj = $this->getTypeObject($option->optionType);
			
			if ($typeObj->compare($this->optionValues[$option->optionName], WCF::getSession()->getPermission($option->optionName)) == 1) {
				throw new UserInputException($option->optionName, 'exceedsOwnPermission');
			}
		}
		else if ($option->optionName == 'admin.user.accessibleGroups' && $this->group !== null && $this->group->isAdminGroup()) {
			$hasOtherAdminGroup = false;
			foreach (UserGroup::getGroupsByType() as $userGroup) {
				if ($userGroup->groupID != $this->group->groupID && $userGroup->isAdminGroup()) {
					$hasOtherAdminGroup = true;
					break;
				}
			}
			
			// prevent users from dropping their own admin state
			if (!$hasOtherAdminGroup) {
				// get type object
				$typeObj = $this->getTypeObject($option->optionType);
				
				if ($typeObj->compare($this->optionValues[$option->optionName], WCF::getSession()->getPermission($option->optionName)) == -1) {
					throw new UserInputException($option->optionName, 'cannotDropPrivileges');
				}
			}
		}
	}
}
