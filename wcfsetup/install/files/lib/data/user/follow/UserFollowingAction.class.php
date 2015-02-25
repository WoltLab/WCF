<?php
namespace wcf\data\user\follow;
use wcf\data\user\UserProfile;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\user\GroupedUserList;
use wcf\system\WCF;

/**
 * Executes following-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.follow
 * @category	Community Framework
 */
class UserFollowingAction extends UserFollowAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\user\follow\UserFollowEditor';
	
	/**
	 * @see	\wcf\data\IGroupedUserListAction::validateGetGroupedUserList()
	 */
	public function validateGetGroupedUserList() {
		$this->readInteger('pageNo');
		$this->readInteger('userID');
		
		$this->userProfile = UserProfile::getUserProfile($this->parameters['userID']);
		if ($this->userProfile->isProtected()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @see	\wcf\data\IGroupedUserListAction::getGroupedUserList()
	 */
	public function getGroupedUserList() {
		// resolve page count
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_user_follow
			WHERE	userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->parameters['userID']));
		$row = $statement->fetchArray();
		$pageCount = ceil($row['count'] / 20);
		
		// get user ids
		$sql = "SELECT	followUserID
			FROM	wcf".WCF_N."_user_follow
			WHERE	userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql, 20, ($this->parameters['pageNo'] - 1) * 20);
		$statement->execute(array($this->parameters['userID']));
		$userIDs = array();
		while ($row = $statement->fetchArray()) {
			$userIDs[] = $row['followUserID'];
		}
		
		// create group
		$group = new GroupedUserList();
		$group->addUserIDs($userIDs);
		
		// load user profiles
		GroupedUserList::loadUsers();
		
		WCF::getTPL()->assign(array(
			'groupedUsers' => array($group)
		));
		
		return array(
			'pageCount' => $pageCount,
			'template' => WCF::getTPL()->fetch('groupedUserList')
		);
	}
}
