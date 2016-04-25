<?php
namespace wcf\data\user\follow;
use wcf\data\user\UserProfile;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IGroupedUserListAction;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\notification\object\UserFollowUserNotificationObject;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\user\GroupedUserList;
use wcf\system\WCF;

/**
 * Executes follower-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.follow
 * @category	Community Framework
 */
class UserFollowAction extends AbstractDatabaseObjectAction implements IGroupedUserListAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$allowGuestAccess
	 */
	protected $allowGuestAccess = array('getGroupedUserList');
	
	/**
	 * user profile object
	 * @var	UserProfile;
	 */
	public $userProfile = null;
	
	/**
	 * Validates given parameters.
	 */
	public function validateFollow() {
		$this->readInteger('userID', false, 'data');
		
		if ($this->parameters['data']['userID'] == WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
		
		// check if current user is ignored by target user
		$sql = "SELECT  ignoreID
			FROM    wcf".WCF_N."_user_ignore
			WHERE   userID = ?
				AND ignoreUserID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$this->parameters['data']['userID'],
			WCF::getUser()->userID
		]);
		
		$ignoreID = $statement->fetchSingleColumn();
		if ($ignoreID !== false) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Follows an user.
	 * 
	 * @return	array
	 */
	public function follow() {
		$follow = UserFollow::getFollow(WCF::getUser()->userID, $this->parameters['data']['userID']);
		
		// not following right now
		if (!$follow->followID) {
			$follow = UserFollowEditor::create(array(
				'userID' => WCF::getUser()->userID,
				'followUserID' => $this->parameters['data']['userID'],
				'time' => TIME_NOW
			));
			
			// send notification
			UserNotificationHandler::getInstance()->fireEvent('following', 'com.woltlab.wcf.user.follow', new UserFollowUserNotificationObject($follow), array($follow->followUserID));
			
			// fire activity event
			UserActivityEventHandler::getInstance()->fireEvent('com.woltlab.wcf.user.recentActivityEvent.follow', $this->parameters['data']['userID']);
			
			// reset storage
			UserStorageHandler::getInstance()->reset(array($this->parameters['data']['userID']), 'followerUserIDs');
			UserStorageHandler::getInstance()->reset(array(WCF::getUser()->userID), 'followingUserIDs');
		}
		
		return array(
			'following' => 1
		);
	}
	
	/**
	 * @see	\wcf\data\user\follow\UserFollowAction::validateFollow()
	 */
	public function validateUnfollow() {
		$this->validateFollow();
	}
	
	/**
	 * Stops following an user.
	 * 
	 * @return	array
	 */
	public function unfollow() {
		$follow = UserFollow::getFollow(WCF::getUser()->userID, $this->parameters['data']['userID']);
		
		if ($follow->followID) {
			$followEditor = new UserFollowEditor($follow);
			$followEditor->delete();
			
			// remove activity event
			UserActivityEventHandler::getInstance()->removeEvents('com.woltlab.wcf.user.recentActivityEvent.follow', array($this->parameters['data']['userID']));
		}
		
		// reset storage
		UserStorageHandler::getInstance()->reset(array($this->parameters['data']['userID']), 'followerUserIDs');
		UserStorageHandler::getInstance()->reset(array(WCF::getUser()->userID), 'followingUserIDs');
		
		return array(
			'following' => 0
		);
	}
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::validateDelete()
	 */
	public function validateDelete() {
		// read objects
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
		
		// validate ownership
		foreach ($this->objects as $follow) {
			if ($follow->userID != WCF::getUser()->userID) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::delete()
	 */
	public function delete() {
		$returnValues = parent::delete();
		
		$followUserIDs = array();
		foreach ($this->objects as $follow) {
			$followUserIDs[] = $follow->followUserID;
			// remove activity event
			UserActivityEventHandler::getInstance()->removeEvents('com.woltlab.wcf.user.recentActivityEvent.follow', array($follow->followUserID));
		}
		
		// reset storage
		UserStorageHandler::getInstance()->reset($followUserIDs, 'followerUserIDs');
		UserStorageHandler::getInstance()->reset(array(WCF::getUser()->userID), 'followingUserIDs');
		
		return $returnValues;
	}
	
	/**
	 * @see	\wcf\data\IGroupedUserListAction::validateGetGroupedUserList()
	 */
	public function validateGetGroupedUserList() {
		$this->readInteger('pageNo');
		$this->readInteger('userID');
		
		$this->userProfile = UserProfileRuntimeCache::getInstance()->getObject($this->parameters['userID']);
		if ($this->userProfile->isProtected()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @see	\wcf\data\IGroupedUserListAction::getGroupedUserList()
	 */
	public function getGroupedUserList() {
		// resolve page count
		$sql = "SELECT	COUNT(*)
			FROM	wcf".WCF_N."_user_follow
			WHERE	followUserID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->parameters['userID']));
		$pageCount = ceil($statement->fetchSingleColumn() / 20);
		
		// get user ids
		$sql = "SELECT	userID
			FROM	wcf".WCF_N."_user_follow
			WHERE	followUserID = ?";
		$statement = WCF::getDB()->prepareStatement($sql, 20, ($this->parameters['pageNo'] - 1) * 20);
		$statement->execute(array($this->parameters['userID']));
		$userIDs = array();
		while ($row = $statement->fetchArray()) {
			$userIDs[] = $row['userID'];
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
