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
 * @copyright	2001-2019 WoltLab GmbH
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
	 * @var	UserGroup
	 */
	protected $group;
	
	/**
	 * true if current user can edit every user group
	 * @var	boolean
	 */
	protected $isAdmin = null;
	
	/**
	 * true if the user is part of the owner group
	 * @var bool
	 * @since 5.2
	 */
	protected $isOwner = null;
	
	/**
	 * List of permission names that may not be altered when the enterprise mode is active.
	 * @var string[]
	 */
	protected $enterpriseBlacklist = [
		// Configuration
		'admin.configuration.canManageApplication',
		'admin.configuration.package.canUpdatePackage',
		'admin.configuration.package.canEditServer',
		
		// User
		'admin.user.canMailUser',
		
		// Management
		'admin.management.canImportData',
		'admin.management.canManageCronjob',
		'admin.management.canRebuildData',
	];
	
	/**
	 * Sets current user group.
	 * 
	 * @param	UserGroup	$group
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
			$this->isAdmin = WCF::getUser()->hasAdministrativeAccess();
		}
		
		return $this->isAdmin;
	}
	
	/**
	 * Returns true, if the current user is a member of the owner group.
	 * 
	 * @return bool
	 * @since 5.2
	 */
	protected function isOwner() {
		if ($this->isOwner === null) {
			$this->isOwner = WCF::getUser()->hasOwnerAccess();
		}
		
		return $this->isOwner;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validateOption(Option $option) {
		parent::validateOption($option);
		
		if ($this->isOwner()) {
			return;
		}
		
		if (ENABLE_ENTERPRISE_MODE && $this->isAdmin() && !in_array($option->optionName, $this->enterpriseBlacklist)) {
			return;
		}
		
		$typeObj = $this->getTypeObject($option->optionType);
		if ($typeObj->compare($this->optionValues[$option->optionName], WCF::getSession()->getPermission($option->optionName)) == 1) {
			throw new UserInputException($option->optionName, 'exceedsOwnPermission');
		}
	}
}
