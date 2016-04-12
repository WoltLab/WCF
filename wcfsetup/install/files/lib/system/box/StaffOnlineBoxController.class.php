<?php
namespace wcf\system\box;
use wcf\data\user\online\UsersOnlineList;
use wcf\system\WCF;

/**
 * Lists staff members who are online.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.box
 * @category	Community Framework
 */
class StaffOnlineBoxController extends AbstractBoxController {
	/**
	 * @inheritDoc
	 */
	protected $supportedPositions = ['sidebarLeft', 'sidebarRight'];
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return WCF::getLanguage()->get('wcf.user.staffOnline'); // @todo
	}
	
	/**
	 * @inheritDoc
	 */
	protected function loadContent() {
		if (MODULE_USERS_ONLINE && WCF::getSession()->getPermission('user.profile.canViewUsersOnlineList')) {
			$usersOnlineList = new UsersOnlineList();
			$usersOnlineList->getConditionBuilder()->add('session.userID IN (SELECT userID FROM wcf'.WCF_N.'_user_to_group WHERE groupID IN (SELECT groupID FROM wcf'.WCF_N.'_user_group WHERE showOnTeamPage = ?))', array(1));
			$usersOnlineList->readObjects();
			
			if (count($usersOnlineList)) {
				WCF::getTPL()->assign([
					'usersOnlineList' => $usersOnlineList
				]);
				
				$this->content = WCF::getTPL()->fetch('boxStaffOnline');
			}
		}
	}
}
