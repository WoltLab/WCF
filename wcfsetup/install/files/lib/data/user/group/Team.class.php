<?php
namespace wcf\data\user\group;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObjectDecorator;

/**
 * Represents a team user group.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Group
 * 
 * @method	UserGroup	getDecoratedObject()
 * @mixin	UserGroup
 */
class Team extends DatabaseObjectDecorator {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = UserGroup::class;
	
	/**
	 * list of user group members
	 * @var	UserProfile[]
	 */
	protected $members = [];
	
	/**
	 * Adds a new member.
	 * 
	 * @param	UserProfile	$user
	 */
	public function addMember(UserProfile $user) {
		$this->members[] = $user;
	}
	
	/**
	 * Returns the list of user group members
	 * 
	 * @return	UserProfile[]
	 */
	public function getMembers() {
		return $this->members;
	}
}
