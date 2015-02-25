<?php
namespace wcf\data\user\group;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObjectDecorator;

/**
 * Represents a team user group.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.group
 * @category	Community Framework
 */
class Team extends DatabaseObjectDecorator {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\group\UserGroup';
	
	/**
	 * list of user group members
	 * @var	array<\wcf\data\user\UserProfile>
	 */
	protected $members = array();
	
	/**
	 * Adds a new member.
	 * 
	 * @param	\wcf\data\user\UserProfile	$user
	 */
	public function addMember(UserProfile $user) {
		$this->members[] = $user;
	}
	
	/**
	 * Returns the list of user group members
	 * 
	 * @return	array<\wcf\data\user\UserProfile>
	 */
	public function getMembers() {
		return $this->members;
	}
}
