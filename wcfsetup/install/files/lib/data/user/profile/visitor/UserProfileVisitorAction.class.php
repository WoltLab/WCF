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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Profile\Visitor
 * 
 * @method	UserProfileVisitor		create()
 * @method	UserProfileVisitorEditor[]	getObjects()
 * @method	UserProfileVisitorEditor	getSingleObject()
 */
class UserProfileVisitorAction extends AbstractDatabaseObjectAction implements IGroupedUserListAction {
	/**
	 * @inheritDoc
	 */
	protected $allowGuestAccess = ['getGroupedUserList'];
	
	/**
	 * user profile object
	 * @var	UserProfile;
	 */
	public $userProfile = null;
	
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
			FROM	wcf".WCF_N."_user_profile_visitor
			WHERE	ownerID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->parameters['userID']]);
		$pageCount = ceil($statement->fetchSingleColumn() / 20);
		
		// get user ids
		$sql = "SELECT		userID
			FROM		wcf".WCF_N."_user_profile_visitor
			WHERE		ownerID = ?
			ORDER BY	time DESC";
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
