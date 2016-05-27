<?php
namespace wcf\system\option\user\group;
use wcf\data\user\group\UserGroup;

/**
 * Default trait for user group option types implementing IUserGroupGroupOptionType.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user.group
 * @category	Community Framework
 * @since	2.2
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
