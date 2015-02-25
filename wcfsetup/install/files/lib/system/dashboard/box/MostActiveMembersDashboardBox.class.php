<?php
namespace wcf\system\dashboard\box;
use wcf\data\dashboard\box\DashboardBox;
use wcf\data\user\UserProfileList;
use wcf\page\IPage;
use wcf\system\cache\builder\MostActiveMembersCacheBuilder;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows a list of the most active members.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.dashboard.box
 * @category	Community Framework
 */
class MostActiveMembersDashboardBox extends AbstractSidebarDashboardBox {
	/**
	 * user profile list
	 * @var	\wcf\data\user\UserProfileList
	 */
	public $userProfileList = null;
	
	/**
	 * @see	\wcf\system\dashboard\box\AbstractDashboardBoxContent::init()
	 */
	public function init(DashboardBox $box, IPage $page) {
		parent::init($box, $page);
		
		// get ids
		$mostActiveMemberIDs = MostActiveMembersCacheBuilder::getInstance()->getData();
		if (!empty($mostActiveMemberIDs)) {
			// get profile data
			$this->userProfileList = new UserProfileList();
			$this->userProfileList->sqlOrderBy = 'user_table.activityPoints DESC';
			$this->userProfileList->setObjectIDs($mostActiveMemberIDs);
			$this->userProfileList->readObjects();
		}
		
		$this->fetched();
	}
	
	/**
	 * @see	\wcf\system\dashboard\box\AbstractContentDashboardBox::render()
	 */
	protected function render() {
		if ($this->userProfileList == null) return '';
		
		if (MODULE_MEMBERS_LIST) {
			$this->titleLink = LinkHandler::getInstance()->getLink('MembersList', array(), 'sortField=activityPoints&sortOrder=DESC');
		}
		WCF::getTPL()->assign(array(
			'mostActiveMembers' => $this->userProfileList
		));
		return WCF::getTPL()->fetch('dashboardBoxMostActiveMembers');
	}
}
