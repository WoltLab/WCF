<?php
namespace wcf\data\user\profile\visitor;
use wcf\data\user\UserProfile;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IGroupedUserListAction;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\user\GroupedUserList;
use wcf\system\WCF;

/**
 * Executes profile visitor-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.profile.visitor
 * @category	Community Framework
 */
class UserProfileVisitorAction extends AbstractDatabaseObjectAction implements IGroupedUserListAction {
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
			FROM	wcf".WCF_N."_user_profile_visitor
			WHERE	ownerID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->parameters['userID']));
		$pageCount = ceil($statement->fetchSingleColumn() / 20);
		
		// get user ids
		$sql = "SELECT		userID
			FROM		wcf".WCF_N."_user_profile_visitor
			WHERE		ownerID = ?
			ORDER BY	time DESC";
		$statement = WCF::getDB()->prepareStatement($sql, 20, ($this->parameters['pageNo'] - 1) * 20);
		$statement->execute(array($this->parameters['userID']));
		$userIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
		
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
