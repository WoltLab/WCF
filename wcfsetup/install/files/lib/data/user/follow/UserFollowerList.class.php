<?php
namespace wcf\data\user\follow;
use wcf\data\user\UserProfile;

/**
 * Represents a list of followers.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.follow
 * @category	Community Framework
 */
class UserFollowerList extends UserFollowList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\user\follow\UserFollow';
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::$objectClassName
	 */
	public $objectClassName = 'wcf\data\user\User';
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::$sqlOrderBy
	 */
	public $sqlOrderBy = 'user_follow.time DESC';
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::__construct()
	 */
	public function __construct() {
		parent::__construct();
		
		$this->sqlSelects .= "user_table.username, user_table.email, user_table.disableAvatar, user_table.enableGravatar, user_table.gravatarFileExtension";
		$this->sqlSelects .= ", user_avatar.*";
		
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user user_table ON (user_table.userID = user_follow.userID)";
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user_avatar user_avatar ON (user_avatar.avatarID = user_table.avatarID)";
	}
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::readObjects()
	 */
	public function readObjects() {
		parent::readObjects();
		
		foreach ($this->objects as &$object) {
			$object = new UserProfile($object);
		}
	}
}
