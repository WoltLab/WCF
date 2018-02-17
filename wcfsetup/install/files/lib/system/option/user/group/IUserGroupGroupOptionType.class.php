<?php
namespace wcf\system\option\user\group;
use wcf\data\user\group\UserGroup;

/**
 * Default interface for user group option types requiring the active user group object.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option\User\Group
 * @since	3.0
 */
interface IUserGroupGroupOptionType {
	/**
	 * Sets the active user group object.
	 * 
	 * @param	UserGroup	$group
	 */
	public function setUserGroup(UserGroup $group);
	
	/**
	 * Returns the active user group object or null.
	 * 
	 * @return	UserGroup
	 */
	public function getUserGroup();
}
