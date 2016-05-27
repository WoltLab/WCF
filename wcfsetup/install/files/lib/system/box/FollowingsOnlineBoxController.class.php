<?php
namespace wcf\system\box;
use wcf\data\user\online\UsersOnlineList;
use wcf\system\WCF;

/**
 * Lists online users the active user is following.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.box
 * @category	Community Framework
 * @since	2.2
 */
class FollowingsOnlineBoxController extends AbstractDatabaseObjectListBoxController {
	/**
	 * @inheritDoc
	 */
	public $defaultLimit = 10;
	
	/**
	 * @inheritDoc
	 */
	protected $supportedPositions = ['sidebarLeft', 'sidebarRight'];
	
	/**
	 * @inheritDoc
	 */
	protected function getObjectList() {
		$objectList = new UsersOnlineList();
		$objectList->getConditionBuilder()->add('session.userID IN (?)', [WCF::getUserProfileHandler()->getFollowingUsers()]);
		
		return $objectList;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getTemplate() {
		return WCF::getTPL()->fetch('boxFollowingsOnline', 'wcf', ['usersOnlineList' => $this->objectList]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasContent() {
		if (!MODULE_USERS_ONLINE || !WCF::getSession()->getPermission('user.profile.canViewUsersOnlineList') || empty(WCF::getUserProfileHandler()->getFollowingUsers())) {
			return false;
		}
		
		return parent::hasContent();
	}
}
