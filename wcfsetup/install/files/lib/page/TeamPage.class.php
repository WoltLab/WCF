<?php
namespace wcf\page;
use wcf\system\breadcrumb\Breadcrumb;
use wcf\system\dashboard\DashboardHandler;
use wcf\system\request\LinkHandler;
use wcf\system\user\collapsible\content\UserCollapsibleContentHandler;
use wcf\system\WCF;

/**
 * Shows the team members list.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category	Community Framework
 */
class TeamPage extends MultipleLinkPage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.user.team';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('user.profile.canViewMembersList');
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('MODULE_TEAM_PAGE');
	
	/**
	 * @see	\wcf\page\AbstractPage::$enableTracking
	 */
	public $enableTracking = true;
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$itemsPerPage
	 */
	public $itemsPerPage = 1000;
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$sortField
	 */
	public $sortField = MEMBERS_LIST_DEFAULT_SORT_FIELD;
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$sortOrder
	 */
	public $sortOrder = MEMBERS_LIST_DEFAULT_SORT_ORDER;
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wcf\data\user\TeamList';
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// add breadcrumbs
		if (MODULE_MEMBERS_LIST) WCF::getBreadcrumbs()->add(new Breadcrumb(WCF::getLanguage()->get('wcf.user.members'), LinkHandler::getInstance()->getLink('MembersList')));
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		DashboardHandler::getInstance()->loadBoxes('com.woltlab.wcf.user.MembersListPage', $this);
		
		WCF::getTPL()->assign(array(
			'sidebarCollapsed' => UserCollapsibleContentHandler::getInstance()->isCollapsed('com.woltlab.wcf.collapsibleSidebar', 'com.woltlab.wcf.user.MembersListPage'),
			'sidebarName' => 'com.woltlab.wcf.user.MembersListPage',
			'allowSpidersToIndexThisPage' => true
		));
	}
}
