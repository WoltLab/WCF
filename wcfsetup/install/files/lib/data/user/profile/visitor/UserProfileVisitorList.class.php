<?php
namespace wcf\data\user\profile\visitor;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of profile visitors.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.profile.visitor
 * @category	Community Framework
 */
class UserProfileVisitorList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$decoratorClassName
	 */
	public $decoratorClassName = 'wcf\data\user\UserProfile';
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::$objectClassName
	 */
	public $objectClassName = 'wcf\data\user\User';
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::$sqlOrderBy
	 */
	public $sqlOrderBy = 'user_profile_visitor.time DESC';
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::__construct()
	 */
	public function __construct() {
		parent::__construct();
		
		$this->sqlSelects .= "user_table.username, user_table.email, user_table.disableAvatar, user_table.enableGravatar, user_table.gravatarFileExtension";
		$this->sqlSelects .= ", user_avatar.*";
		
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user user_table ON (user_table.userID = user_profile_visitor.userID)";
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user_avatar user_avatar ON (user_avatar.avatarID = user_table.avatarID)";
	}
}
