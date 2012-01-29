<?php
namespace wcf\system\option\user\group;
use wcf\data\user\group\UserGroup;
use wcf\system\exception\SystemException;
use wcf\system\option\OptionHandler;
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
	 * Sets current user group.
	 * 
	 * @param	wcf\data\user\group\UserGroup	$group
	 */
	public function setUserGroup(UserGroup $group) {
		$this->group = $group;
	}
	
	/**
	 * @see	wcf\system\option\OptionHandler::getClassName()
	 */
	protected function getClassName($type) {
		$className = parent::getClassName($type);
		
		if ($className === null) {
			$className = 'wcf\system\option\user\group\\'.ucfirst($type).'UserGroupOptionType';
			
			// validate class
			if (!class_exists($className)) {
				return null;
			}
			if (!ClassUtil::isInstanceOf($className, 'wcf\system\option\IOptionType')) {
				throw new SystemException("'".$className."' should implement wcf\system\option\IOptionType");
			}
		}
		
		return $className;
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