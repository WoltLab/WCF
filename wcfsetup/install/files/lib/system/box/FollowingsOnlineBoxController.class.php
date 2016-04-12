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
 */
class FollowingsOnlineBoxController extends AbstractBoxController {
	/**
	 * @inheritDoc
	 */
	protected $supportedPositions = ['sidebarLeft', 'sidebarRight'];
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return WCF::getLanguage()->get('wcf.user.followingsOnline'); // @todo
	}
	
	/**
	 * @inheritDoc
	 */
	protected function loadContent() {
		if (MODULE_USERS_ONLINE && WCF::getSession()->getPermission('user.profile.canViewUsersOnlineList') && count(WCF::getUserProfileHandler()->getFollowingUsers())) {
			$usersOnlineList = new UsersOnlineList();
			$usersOnlineList->getConditionBuilder()->add('session.userID IN (?)', array(WCF::getUserProfileHandler()->getFollowingUsers()));
			$usersOnlineList->sqlLimit = 10;
			$usersOnlineList->readObjects();
			
			if (count($usersOnlineList)) {
				WCF::getTPL()->assign([
					'usersOnlineList' => $usersOnlineList
				]);
				
				$this->content = WCF::getTPL()->fetch('boxFollowingsOnline');
			}
		}
	}
}
