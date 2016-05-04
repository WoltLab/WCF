<?php
namespace wcf\system\box;
use wcf\data\user\online\UsersOnlineList;
use wcf\system\WCF;

/**
 * Box controller for a list of staff members who are currently online.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.box
 * @category	Community Framework
 * @since	2.2
 */
class StaffOnlineListBoxController extends AbstractDatabaseObjectListBoxController {
	/**
	 * @inheritDoc
	 */
	protected $supportedPositions = ['sidebarLeft', 'sidebarRight'];
	
	/**
	 * @inheritDoc
	 */
	protected function getObjectList() {
		$objectList = new UsersOnlineList();
		$objectList->getConditionBuilder()->add('session.userID IN (SELECT userID FROM wcf'.WCF_N.'_user_to_group WHERE groupID IN (SELECT groupID FROM wcf'.WCF_N.'_user_group WHERE showOnTeamPage = ?))', [1]);
		
		return $objectList;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getTemplate() {
		return WCF::getTPL()->fetch('boxStaffOnline', 'wcf', ['usersOnlineList' => $this->objectList]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasContent() {
		if (!MODULE_USERS_ONLINE || WCF::getSession()->getPermission('user.profile.canViewUsersOnlineList')) {
			return false;
		}
		
		return parent::hasContent();
	}
}
