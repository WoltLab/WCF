<?php
namespace wcf\system\option\user\group;
use wcf\data\user\group\UserGroup;
use wcf\data\option\Option;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\option\OptionHandler;
use wcf\system\WCF;
use wcf\util\ClassUtil;

/**
 * Handles user group options.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user.group
 * @category 	Community Framework
 */
class UserGroupOptionHandler extends OptionHandler {
	/**
	 * user group object
	 * @var	wcf\data\user\group\UserGroup
	 */
	protected $group = null;
	
	/**
	* UserGroupType object cache.
	* 
	* @var array<wcf\system\option\user\group\IUserGroupOptionType>
	*/
	public $userGroupTypeObjects = array();
	
	/**
	 * Sets current user group.
	 * 
	 * @param	wcf\data\user\group\UserGroup	$group
	 */
	public function setUserGroup(UserGroup $group) {
		$this->group = $group;
	}
	
	/**
	 * @see wcf\system\option\OptionHandler::getClassName()
	 */
	protected function getClassName($type) {
		$className = parent::getClassName($type);
		
		if ($className === null) {
			$className = $this->getUserGroupClassName($type);
		}
		
		return $className;
	}
	
	/**
	 * @like	wcf\system\option\OptionHandler::getTypeObject()
	 */
	protected function getUserGroupTypeObject($type) {
		if (!isset($this->userGroupTypeObjects[$type])) {
			$className = $this->getUserGroupClassName($type);
			if ($className === null) {
				throw new SystemException("unable to find class for option type '".$type."'");
			}
				
			// create instance
			$this->userGroupTypeObjects[$type] = new $className();
		}
		
		return $this->userGroupTypeObjects[$type];
	}
	
	/**
	 * @like wcf\system\option\OptionHandler::getClassName()
	 */
	private function getUserGroupClassName($type) {
		$className = 'wcf\system\option\user\group\\'.ucfirst($type).'UserGroupOptionType';
			
		// validate class
		if (!class_exists($className)) {
			return null;
		}
		if (!ClassUtil::isInstanceOf($className, 'wcf\system\option\IOptionType')) {
			throw new SystemException("'".$className."' should implement wcf\system\option\IOptionType");
		}
		
		return $className;
	}
	
	/**
	 * @see wcf\system\option\OptionHandler::getCategoryOptions()
	 */
	public function getCategoryOptions($categoryName = '', $inherit = true) {
		$options = parent::getCategoryOptions($categoryName, $inherit);
		$children = array();
		
		foreach ($options as $option) {
			$useOption = true;
			
			// handle options that can be hidden
			switch ($option['object']->optionType) {
				case 'boolean':
					if (!WCF::getSession()->getPermission($option['object']->optionName)) {
						$useOption = false;
					}
			}
			
			if (!self::usePermissionCheck() || ($useOption && $option['html'] !== null)) {
				$children[] = $option;
			}
		}
		
		return $children;
	}
	
	/**
	 * @see	wcf\system\option\OptionHandler::validateOption()
	 */
	protected function validateOption(Option $option) {
		parent::validateOption($option);
		
		if (self::usePermissionCheck()) {
			// get the type object
			$typeObj = $this->getUserGroupTypeObject($option->optionType);
			
			// check the permissions of the user
			$typeObj->checkPermissions($option, (isset($this->rawValues[$option->optionName]) ? $this->rawValues[$option->optionName] : null));
		}
	}
	
	/**
	 * Checks if the permission check is enabled or not.
	 * 
	 * @return	bool
	 */
	public static function usePermissionCheck() {
		if (in_array(4, WCF::getUser()->getGroupIDs())) {
			return false;
		}
		return true;
	}
	
	/**
	 * @see	wcf\system\option\IOptionHandler::readData()
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
}