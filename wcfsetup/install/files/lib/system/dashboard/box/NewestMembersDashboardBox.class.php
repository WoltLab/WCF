<?php
namespace wcf\system\dashboard\box;
use wcf\data\dashboard\box\DashboardBox;
use wcf\data\user\UserProfileList;
use wcf\page\IPage;
use wcf\system\cache\builder\NewestMembersCacheBuilder;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows a list of the newest members.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.dashboard.box
 * @category	Community Framework
 */
class NewestMembersDashboardBox extends AbstractSidebarDashboardBox {
	/**
	 * user profile list
	 * @var	\wcf\data\user\UserProfileList
	 */
	public $userProfileList = null;
	
	/**
	 * @see	\wcf\system\dashboard\box\IDashboardBox::init()
	 */
	public function init(DashboardBox $box, IPage $page) {
		parent::init($box, $page);
		
		// get ids
		$newestMemberIDs = NewestMembersCacheBuilder::getInstance()->getData();
		if (!empty($newestMemberIDs)) {
			// get profile data
			$this->userProfileList = new UserProfileList();
			$this->userProfileList->sqlOrderBy = 'user_table.registrationDate DESC';
			$this->userProfileList->setObjectIDs($newestMemberIDs);
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
			$this->titleLink = LinkHandler::getInstance()->getLink('MembersList', array(), 'sortField=registrationDate&sortOrder=DESC');
		}
		WCF::getTPL()->assign(array(
			'newestMembers' => $this->userProfileList
		));
		return WCF::getTPL()->fetch('dashboardBoxNewestMembers');
	}
}
