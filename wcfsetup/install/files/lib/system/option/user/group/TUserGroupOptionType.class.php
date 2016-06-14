<?php
namespace wcf\system\option\user\group;
use wcf\data\user\group\UserGroup;

/**
 * Default trait for user group option types implementing IUserGroupGroupOptionType.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option\User\Group
 * @since	3.0
 */
trait TUserGroupOptionType {
	/**
	 * user group object
	 * @var	\wcf\data\user\group\UserGroup
	 */
	protected $userGroup = null;
	
	/**
	 * @inheritDoc
	 */
	public function setUserGroup(UserGroup $group) {
		$this->userGroup = $group;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getUserGroup() {
		return $this->userGroup;
	}
}
