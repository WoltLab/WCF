<?php
namespace wcf\system\option\user\group;
use wcf\data\user\group\UserGroup;

/**
 * Default interface for user group option types requiring the active user group object.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user.group
 * @category	Community Framework
 * @since	2.2
 */
interface IUserGroupGroupOptionType {
	/**
	 * Sets the active user group object.
	 * 
	 * @param	\wcf\data\user\group\UserGroup		$group
	 */
	public function setUserGroup(UserGroup $group);
	
	/**
	 * Returns the active user group object or null.
	 * 
	 * @return	\wcf\data\user\group\UserGroup
	 */
	public function getUserGroup();
}
