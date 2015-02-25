<?php
namespace wcf\system\option\user\group;
use wcf\data\option\Option;
use wcf\data\user\group\UserGroup;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\option\OptionHandler;
use wcf\util\ClassUtil;
use wcf\system\WCF;

/**
 * Handles user group options.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user.group
 * @category	Community Framework
 */
class UserGroupOptionHandler extends OptionHandler {
	/**
	 * @see	\wcf\system\option\OptionHandler::$cacheClass
	 */
	protected $cacheClass = 'wcf\system\cache\builder\UserGroupOptionCacheBuilder';
	
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
	 * @see	\wcf\system\option\OptionHandler::checkOption()
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
	 * @see	\wcf\system\option\OptionHandler::getClassName()
	 */
	protected function getClassName($type) {
		$className = 'wcf\system\option\user\group\\'.ucfirst($type).'UserGroupOptionType';
		
		// validate class
		if (!class_exists($className)) {
			return null;
		}
		if (!ClassUtil::isInstanceOf($className, 'wcf\system\option\user\group\IUserGroupOptionType')) {
			throw new SystemException("'".$className."' does not implement 'wcf\system\option\user\group\IUserGroupOptionType'");
		}
		
		return $className;
	}
	
	/**
	 * @see	\wcf\system\option\IOptionHandler::readData()
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
	 * @see	\wcf\system\option\OptionHandler::validateOption()
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
