<?php
namespace wcf\system\dashboard\box;
use wcf\data\dashboard\box\DashboardBox;
use wcf\data\user\online\UsersOnlineList;
use wcf\page\IPage;
use wcf\system\WCF;

/**
 * Lists staff members who are online.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.dashboard.box
 * @category	Community Framework
 */
class StaffOnlineSidebarDashboardBox extends AbstractSidebarDashboardBox {
	/**
	 * users online list
	 * @var	\wcf\data\user\online\UsersOnlineList
	 */
	public $usersOnlineList = null;
	
	/**
	 * @see	\wcf\system\dashboard\box\IDashboardBox::init()
	 */
	public function init(DashboardBox $box, IPage $page) {
		parent::init($box, $page);
		
		if (MODULE_USERS_ONLINE) {
			$this->usersOnlineList = new UsersOnlineList();
			$this->usersOnlineList->getConditionBuilder()->add('session.userID IN (SELECT userID FROM wcf'.WCF_N.'_user_to_group WHERE groupID IN (SELECT groupID FROM wcf'.WCF_N.'_user_group WHERE showOnTeamPage = ?))', array(1));
			$this->usersOnlineList->readObjects();
		}
		
		$this->fetched();
	}
	
	/**
	 * @see	\wcf\system\dashboard\box\AbstractContentDashboardBox::render()
	 */
	protected function render() {
		if (empty($this->usersOnlineList) || !count($this->usersOnlineList->getObjects())) {
			return '';
		}
		
		WCF::getTPL()->assign(array(
			'usersOnlineList' => $this->usersOnlineList
		));
		return WCF::getTPL()->fetch('dashboardBoxStaffOnlineSidebar');
	}
}
