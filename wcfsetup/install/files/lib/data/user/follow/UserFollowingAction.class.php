<?php
namespace wcf\data\user\follow;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\user\GroupedUserList;
use wcf\system\WCF;

/**
 * Executes following-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Follow
 */
class UserFollowingAction extends UserFollowAction {
	/**
	 * @inheritDoc
	 */
	protected $className = UserFollowEditor::class;
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function getGroupedUserList() {
		// resolve page count
		$sql = "SELECT	COUNT(*)
			FROM	wcf".WCF_N."_user_follow
			WHERE	userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->parameters['userID']]);
		$pageCount = ceil($statement->fetchSingleColumn() / 20);
		
		// get user ids
		$sql = "SELECT	followUserID
			FROM	wcf".WCF_N."_user_follow
			WHERE	userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql, 20, ($this->parameters['pageNo'] - 1) * 20);
		$statement->execute([$this->parameters['userID']]);
		$userIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
		
		// create group
		$group = new GroupedUserList();
		$group->addUserIDs($userIDs);
		
		// load user profiles
		GroupedUserList::loadUsers();
		
		WCF::getTPL()->assign([
			'groupedUsers' => [$group]
		]);
		
		return [
			'pageCount' => $pageCount,
			'template' => WCF::getTPL()->fetch('groupedUserList')
		];
	}
}
